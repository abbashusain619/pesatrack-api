<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\PaymentProviders\AccountProviderInterface;
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
            'currency'              => 'nullable|string|size:3', // now nullable – we auto-set for mobile money
            'balance'               => 'nullable|numeric|min:0',
            'mobile_provider'       => 'nullable|string|required_if:type,mobile_money',
            'phone_number'          => 'nullable|string|required_if:type,mobile_money',
            'bank_account_number'   => 'nullable|string|required_if:type,bank',
            'bank_code'             => 'nullable|string|required_if:type,bank',
        ]);

        // Auto‑set currency for mobile money based on provider
        if ($validated['type'] === 'mobile_money' && isset($validated['mobile_provider'])) {
            $currencyMap = [
                'mpesa_tz'   => 'TZS', 'tigo_pesa' => 'TZS', 'airtel_tz'  => 'TZS',
                'halopesa'   => 'TZS', 'azampesa'  => 'TZS',
                'mpesa_ke'   => 'KES', 'airtel_ke' => 'KES',
                'mtn_mobile_money' => 'UGX', 'vodafone_cash' => 'GHS',
            ];
            $validated['currency'] = $currencyMap[$validated['mobile_provider']] ?? 'TZS';
        }

        // Fallback currency if still empty
        if (empty($validated['currency'])) {
            $validated['currency'] = 'TZS';
        }

        // Verify the account using the provider wrapper (mock for now)
        $identifier = $validated['phone_number'] ?? $validated['bank_account_number'] ?? null;
        if ($identifier) {
            $verification = $provider->verifyAccount($identifier, $validated['type']);
            if (!$verification['valid']) {
                return back()->withInput()->withErrors(['account' => $verification['message']]);
            }
        }

        $account = Account::create([
            'user_id'               => Auth::id(),
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

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'type'      => 'sometimes|in:mobile_money,bank,cash',
            'currency'  => 'sometimes|string|size:3',
            'balance'   => 'sometimes|numeric|min:0',
        ]);

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