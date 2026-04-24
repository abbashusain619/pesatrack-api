<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
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

        // Validation rules aligned with web form
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|in:mobile_money,bank,cash',
            'currency'           => 'nullable|string|size:3',   // will be auto-set for mobile money
            'balance'            => 'nullable|numeric|min:0',
            'mobile_provider'    => 'nullable|string|required_if:type,mobile_money',
            'phone_number'       => 'nullable|string|required_if:type,mobile_money',
            'bank_account_number'=> 'nullable|string|required_if:type,bank',
            'bank_code'          => 'nullable|string|required_if:type,bank',
        ]);

        // Auto-set currency for mobile money based on provider
        if ($validated['type'] === 'mobile_money' && isset($validated['mobile_provider'])) {
            $currencyMap = [
                'mpesa_tz' => 'TZS', 'tigo_pesa' => 'TZS', 'airtel_tz' => 'TZS',
                'halopesa' => 'TZS', 'azampesa' => 'TZS',
                'mpesa_ke' => 'KES', 'airtel_ke' => 'KES',
                'mtn_mobile_money' => 'UGX', 'vodafone_cash' => 'GHS',
            ];
            $validated['currency'] = $currencyMap[$validated['mobile_provider']] ?? 'TZS';
        }

        // Ensure currency is set (fallback)
        if (empty($validated['currency'])) {
            $validated['currency'] = 'TZS';
        }

        // Verify account using the provider wrapper (mock for now)
        $identifier = $validated['phone_number'] ?? $validated['bank_account_number'] ?? null;
        if ($identifier) {
            $verification = $provider->verifyAccount($identifier, $validated['type']);
            if (!$verification['valid']) {
                return response()->json(['error' => $verification['message']], 422);
            }
            // Optionally store verified holder name
            $validated['verified_holder'] = $verification['account_holder'] ?? null;
        }

        $account = Account::create([
            'user_id'               => auth()->id(),
            'name'                  => $validated['name'],
            'type'                  => $validated['type'],
            'currency'              => strtoupper($validated['currency']),
            'balance'               => $validated['balance'] ?? 0,
            'mobile_provider'       => $validated['mobile_provider'] ?? null,
            'phone_number'          => $validated['phone_number'] ?? null,
            'bank_account_number'   => $validated['bank_account_number'] ?? null,
            'bank_code'             => $validated['bank_code'] ?? null,
            'verified_at'           => now(),
            'verification_status'   => 'verified',
        ]);

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

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'type'     => 'sometimes|in:mobile_money,bank,cash',
            'currency' => 'sometimes|string|size:3',
            'balance'  => 'sometimes|numeric|min:0',
        ]);

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