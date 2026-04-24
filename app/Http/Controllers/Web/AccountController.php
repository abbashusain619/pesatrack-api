<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'type'                  => 'required|in:mobile_money,bank,cash',
            'currency'              => 'required|string|size:3',
            'balance'               => 'nullable|numeric|min:0',
            'mobile_provider'       => 'nullable|string|required_if:type,mobile_money',
            'phone_number'          => 'nullable|string|required_if:type,mobile_money',
            'bank_account_number'   => 'nullable|string|required_if:type,bank',
            'bank_code'             => 'nullable|string|required_if:type,bank',
        ]);

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
        return redirect()->route('accounts.index')->with('success', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Account deleted.');
    }
}