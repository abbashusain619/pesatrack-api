<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Models\Transaction; 
use App\Services\PaymentProviders\AccountProviderInterface;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', auth()->id())->get();
        return response()->json($accounts);
    }

    public function store(Request $request, AccountProviderInterface $provider)
    {
        \Log::info('API Account store - full request', $request->all());

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|in:mobile_money,bank,cash',
            'currency'           => 'nullable|string|size:3',
            'balance'            => 'nullable|numeric|min:0',
            'mobile_provider'    => 'nullable|string|required_if:type,mobile_money',
            'phone_number'       => 'nullable|string|required_if:type,mobile_money',
            'bank_account_number'=> 'nullable|string|required_if:type,bank',
            'bank_code'          => 'nullable|string|required_if:type,bank',
        ]);

        // Auto-set currency
        if ($validated['type'] === 'mobile_money' && isset($validated['mobile_provider'])) {
            $currencyMap = [
                'mpesa_tz' => 'TZS', 'tigo_pesa' => 'TZS', 'airtel_tz' => 'TZS',
                'halopesa' => 'TZS', 'azampesa' => 'TZS',
                'mpesa_ke' => 'KES', 'airtel_ke' => 'KES',
                'mtn_mobile_money' => 'UGX', 'vodafone_cash' => 'GHS',
            ];
            $validated['currency'] = $currencyMap[$validated['mobile_provider']] ?? 'TZS';
        }
        if (empty($validated['currency'])) {
            $validated['currency'] = 'TZS';
        }

        $identifier = $validated['phone_number'] ?? $validated['bank_account_number'] ?? null;
        $providerName = $validated['mobile_provider'] ?? $validated['bank_code'] ?? null;

        // Verify
        if ($identifier) {
            $verification = $provider->verifyAccount($identifier, $validated['type'], $providerName);
            if (!$verification['valid']) {
                return response()->json(['error' => $verification['message']], 422);
            }
        }

        $isSynced = in_array($validated['type'], ['mobile_money', 'bank']);
        $initialBalance = $validated['balance'] ?? 0;
        $syncedTransactions = [];

        // Fetch balance and transactions for synced accounts
        if ($isSynced && $identifier) {
            $balanceData = $provider->getBalance($identifier, $validated['type'], $providerName);
            if ($balanceData['success']) {
                $initialBalance = $balanceData['balance'];
                if (isset($balanceData['currency'])) {
                    $validated['currency'] = $balanceData['currency'];
                }
            }

            $txData = $provider->getTransactions($identifier, 30, $validated['type'], $providerName);
            if ($txData['success']) {
                $syncedTransactions = $txData['transactions'];
            }
        }

        $account = Account::create([
            'user_id'               => auth()->id(),
            'name'                  => $validated['name'],
            'type'                  => $validated['type'],
            'currency'              => strtoupper($validated['currency']),
            'balance'               => $initialBalance,
            'mobile_provider'       => $validated['mobile_provider'] ?? null,
            'phone_number'          => $validated['phone_number'] ?? null,
            'bank_account_number'   => $validated['bank_account_number'] ?? null,
            'bank_code'             => $validated['bank_code'] ?? null,
            'verified_at'           => now(),
            'verification_status'   => 'verified',
            'is_synced'             => $isSynced,
        ]);

        foreach ($syncedTransactions as $tx) {
            Transaction::create([
                'user_id'           => auth()->id(),
                'account_id'        => $account->id,
                'category_id'       => null,
                'type'              => $tx['type'] ?? 'expense',
                'amount'            => $tx['amount'],
                'description'       => $tx['description'] ?? null,
                'reference'         => $tx['reference'] ?? null,
                'transaction_date'  => $tx['transaction_date'] ?? now(),
                'is_synced'         => true,
            ]);
        }

        return response()->json($account, 201);
    }

    public function show(Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($account);
    }

    public function update(Request $request, Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($account->is_synced) {
            // Synced accounts: only name can be changed
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
            ]);
            if (empty($validated)) {
                return response()->json(['message' => 'No valid fields to update'], 422);
            }
            $account->update($validated);
            return response()->json($account);
        }

        // Manual accounts: allow name, type, currency, balance
        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'type'     => 'sometimes|in:mobile_money,bank,cash',
            'currency' => 'sometimes|string|size:3',
            'balance'  => 'sometimes|numeric|min:0',
        ]);

        // Prevent converting a manual account into a synced one
        if (isset($validated['type']) && in_array($validated['type'], ['mobile_money', 'bank'])) {
            return response()->json(['message' => 'Cannot convert manual account to synced'], 422);
        }

        $account->update($validated);
        return response()->json($account);
    }

    public function destroy(Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $account->delete();
        return response()->json(['message' => 'Account deleted']);
    }
}