<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountVerificationService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', auth()->id())->get();
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request, AccountVerificationService $verifier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:mpesa,bank,cash',
            'currency' => 'required|string|size:3',
            'balance' => 'nullable|numeric|min:0',
            'phone_number' => 'nullable|string|required_if:type,mpesa',
            'bank_account_number' => 'nullable|string|required_if:type,bank',
            'bank_code' => 'nullable|string|required_if:type,bank',
        ]);

        // Verification logic
        if ($validated['type'] === 'mpesa') {
            $verification = $verifier->verifyMpesa($validated['phone_number']);
            if (!$verification['valid']) {
                return back()->withErrors(['phone_number' => $verification['message']])->withInput();
            }
        }

        if ($validated['type'] === 'bank') {
            $verification = $verifier->verifyBank($validated['bank_account_number'], $validated['bank_code']);
            if (!$verification['valid']) {
                return back()->withErrors(['bank_account_number' => $verification['message']])->withInput();
            }
        }

        $account = Account::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency' => strtoupper($validated['currency']),
            'balance' => $validated['balance'] ?? 0,
            'phone_number' => $validated['phone_number'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_code' => $validated['bank_code'] ?? null,
            'verified_at' => now(),
            'verification_status' => 'verified',
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account created.');
    }

    public function edit(Account $account)
    {
        if ($account->user_id !== auth()->id()) abort(403);
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account, AccountVerificationService $verifier)
    {
        if ($account->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:mpesa,bank,cash',
            'currency' => 'sometimes|string|size:3',
            'balance' => 'sometimes|numeric|min:0',
            'phone_number' => 'nullable|string|required_if:type,mpesa',
            'bank_account_number' => 'nullable|string|required_if:type,bank',
            'bank_code' => 'nullable|string|required_if:type,bank',
        ]);

        // Verification logic for changed fields
        if (isset($validated['type']) && $validated['type'] === 'mpesa') {
            $phone = $validated['phone_number'] ?? $account->phone_number;
            if ($phone && $phone !== $account->phone_number) {
                $verification = $verifier->verifyMpesa($phone);
                if (!$verification['valid']) {
                    return back()->withErrors(['phone_number' => $verification['message']])->withInput();
                }
                $validated['phone_number'] = $phone;
            }
            // If type changed to mpesa, clear bank fields
            if ($account->type !== 'mpesa') {
                $validated['bank_account_number'] = null;
                $validated['bank_code'] = null;
            }
        } elseif (isset($validated['type']) && $validated['type'] === 'bank') {
            $accountNumber = $validated['bank_account_number'] ?? $account->bank_account_number;
            $bankCode = $validated['bank_code'] ?? $account->bank_code;
            if (($accountNumber && $accountNumber !== $account->bank_account_number) ||
                ($bankCode && $bankCode !== $account->bank_code)) {
                $verification = $verifier->verifyBank($accountNumber, $bankCode);
                if (!$verification['valid']) {
                    return back()->withErrors(['bank_account_number' => $verification['message']])->withInput();
                }
                $validated['bank_account_number'] = $accountNumber;
                $validated['bank_code'] = $bankCode;
            }
            // If type changed to bank, clear mpesa field
            if ($account->type !== 'bank') {
                $validated['phone_number'] = null;
            }
        } elseif (isset($validated['type']) && $validated['type'] === 'cash') {
            // Clear both mpesa and bank fields if switching to cash
            $validated['phone_number'] = null;
            $validated['bank_account_number'] = null;
            $validated['bank_code'] = null;
        }

        // Only update fields that are present in validated data
        $account->update($validated);

        return redirect()->route('accounts.index')->with('success', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        if ($account->user_id !== auth()->id()) abort(403);
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Account deleted.');
    }
}