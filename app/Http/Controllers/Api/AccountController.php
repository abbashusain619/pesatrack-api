<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', auth()->id())->get();
        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:mpesa,bank,cash',
            'currency' => 'required|string|size:3',
            'balance' => 'nullable|numeric|min:0',
        ]);

        $account = Account::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency' => $validated['currency'],
            'balance' => $validated['balance'] ?? 0,
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