<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\AccountVerificationService;


class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', auth()->id())->get();
        return response()->json($accounts);
    }

    public function store(Request $request, AccountVerificationService $verifier)
{
    // Log full request
    \Log::info('Account store - full request', $request->all());

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|in:mpesa,bank,cash',
        'currency' => 'required|string|size:3',
        'balance' => 'nullable|numeric|min:0',
        'phone_number' => 'nullable|string|required_if:type,mpesa',
        'bank_account_number' => 'nullable|string|required_if:type,bank',
        'bank_code' => 'nullable|string|required_if:type,bank',
    ]);

    \Log::info('After validation', $validated);

    if ($validated['type'] === 'mpesa') {
        $verification = $verifier->verifyMpesa($validated['phone_number']);
        \Log::info('Verification result', $verification);
        if (!$verification['valid']) {
            return response()->json(['error' => $verification['message']], 422);
        }
    }

        if ($validated['type'] === 'bank') {
            $verification = $verifier->verifyBank($validated['bank_account_number'], $validated['bank_code']);
            if (!$verification['valid']) {
                return response()->json(['error' => $verification['message']], 422);
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
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:mpesa,bank,cash',
            'currency' => 'sometimes|string|size:3',
            'balance' => 'sometimes|numeric|min:0',
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