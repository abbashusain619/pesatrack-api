<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:mpesa,bank,cash',
            'currency' => 'required|string|size:3',
            'balance' => 'nullable|numeric|min:0',
        ]);

        Account::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency' => strtoupper($validated['currency']),
            'balance' => $validated['balance'] ?? 0,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account created.');
    }

    public function edit(Account $account)
    {
        if ($account->user_id !== auth()->id()) abort(403);
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        if ($account->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:mpesa,bank,cash',
            'currency' => 'sometimes|string|size:3',
            'balance' => 'sometimes|numeric|min:0',
        ]);
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