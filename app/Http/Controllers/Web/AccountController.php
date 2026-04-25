<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\PaymentProviders\AccountProviderInterface;
use App\Models\Transaction; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', Auth::id())->get();
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request, AccountProviderInterface $provider)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'type'                  => 'required|in:mobile_money,bank,cash',
            'currency'              => 'nullable|string|size:3',
            'balance'               => 'nullable|numeric|min:0',
            'mobile_provider'       => 'nullable|string|required_if:type,mobile_money',
            'phone_number'          => 'nullable|string|required_if:type,mobile_money',
            'bank_account_number'   => 'nullable|string|required_if:type,bank',
            'bank_code'             => 'nullable|string|required_if:type,bank',
        ]);

        $identifier = $validated['phone_number'] ?? $validated['bank_account_number'] ?? null;
        $providerName = $validated['mobile_provider'] ?? $validated['bank_code'] ?? null;

        // 1. Verify the account
        if ($identifier) {
            $verification = $provider->verifyAccount($identifier, $validated['type'], $providerName);
            if (!$verification['valid']) {
                return back()->withInput()->withErrors(['account' => $verification['message']]);
            }
        }

        $isSynced = in_array($validated['type'], ['mobile_money', 'bank']);
        $initialBalance = $validated['balance'] ?? 0;
        $syncedTransactions = [];

        // 2. If synced, fetch balance and recent transactions
        if ($isSynced && $identifier) {
            $balanceData = $provider->getBalance($identifier, $validated['type'], $providerName);
            if ($balanceData['success']) {
                $initialBalance = $balanceData['balance'];
                // Always use API currency for synced accounts; override any user input
                if (isset($balanceData['currency'])) {
                    $validated['currency'] = $balanceData['currency'];
                }
            }

            $txData = $provider->getTransactions($identifier, 30, $validated['type'], $providerName);
            if ($txData['success']) {
                $syncedTransactions = $txData['transactions'];
            }
        } else {
            // For cash accounts, ensure currency is set (fallback to TZS if empty)
            if (empty($validated['currency'])) {
                $validated['currency'] = 'TZS';
            }
        }

        // 3. Create account
        $account = Account::create([
            'user_id'               => Auth::id(),
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

        // 4. Insert synced transactions
        foreach ($syncedTransactions as $tx) {
            Transaction::create([
                'user_id'           => Auth::id(),
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

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function show(Account $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
        return view('accounts.show', compact('account'));
    }

    public function edit(Account $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }

        if ($account->is_synced) {
            // Synced accounts can only edit their name
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);
            $account->update($validated);
            return redirect()->route('accounts.index')->with('success', 'Account name updated.');
        }

        // Manual account (cash): allow editing name, type, currency, balance
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:mobile_money,bank,cash',
            'currency' => 'nullable|string|size:3',
            'balance'  => 'nullable|numeric|min:0',
        ]);

        // Prevent converting a manual account into a synced one (would break sync logic)
        if (in_array($validated['type'], ['mobile_money', 'bank'])) {
            return back()->withErrors(['type' => 'Cannot convert a manual account to a synced account. Please delete and create a new synced account instead.']);
        }

        $account->update($validated);
        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
    }
}