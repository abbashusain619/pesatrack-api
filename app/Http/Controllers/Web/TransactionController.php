<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('user_id', auth()->id())
            ->with('account')
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $accounts = Account::where('user_id', auth()->id())->get();
        return view('transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'transaction_date' => 'nullable|date',
        ]);

        $account = Account::findOrFail($validated['account_id']);

        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'account_id' => $validated['account_id'],
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'transaction_date' => $validated['transaction_date'] ?? now(),
            ]);

            if ($validated['type'] === 'income') {
                $account->balance += $validated['amount'];
            } else {
                $account->balance -= $validated['amount'];
            }
            $account->save();

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaction failed.');
        }
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        DB::beginTransaction();

        try {
            $account = $transaction->account;
            if ($transaction->type === 'income') {
                $account->balance -= $transaction->amount;
            } else {
                $account->balance += $transaction->amount;
            }
            $account->save();

            $transaction->delete();

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Deletion failed.');
        }
    }
}