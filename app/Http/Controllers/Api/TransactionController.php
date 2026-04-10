<?php

namespace App\Http\Controllers\Api;

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
            ->get();
        return response()->json($transactions);
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
            return response()->json(['message' => 'Unauthorized'], 403);
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
            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Transaction failed'], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
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
            return response()->json(['message' => 'Transaction deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Deletion failed'], 500);
        }
    }
}