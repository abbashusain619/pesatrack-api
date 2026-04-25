<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('user_id', auth()->id())
            ->with(['account', 'category'])
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
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $account = Account::findOrFail($validated['account_id']);

        if ($account->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify category belongs to user or is system category
        if (isset($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
            if ($category && $category->user_id && $category->user_id !== auth()->id()) {
                return response()->json(['message' => 'Invalid category'], 422);
            }
        }

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'account_id' => $validated['account_id'],
                'category_id' => $validated['category_id'] ?? null,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'transaction_date' => $validated['transaction_date'] ?? now(),
                // is_synced defaults to false
            ]);

            if ($validated['type'] === 'income') {
                $account->balance += $validated['amount'];
            } else {
                $account->balance -= $validated['amount'];
            }
            $account->save();

            DB::commit();
            return response()->json($transaction->load(['account', 'category']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($transaction->load(['account', 'category']));
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Synced transactions: only category can be updated
        if ($transaction->is_synced) {
            $validated = $request->validate([
                'category_id' => 'nullable|exists:categories,id',
            ]);

            if (isset($validated['category_id']) && $validated['category_id']) {
                $category = Category::find($validated['category_id']);
                if ($category && $category->user_id && $category->user_id !== auth()->id()) {
                    return response()->json(['message' => 'Invalid category'], 422);
                }
            }

            $transaction->update($validated);
            return response()->json($transaction->load(['account', 'category']));
        }

        // Manual transactions: allow non‑financial updates
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'transaction_date' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if (isset($validated['category_id']) && $validated['category_id']) {
            $category = Category::find($validated['category_id']);
            if ($category && $category->user_id && $category->user_id !== auth()->id()) {
                return response()->json(['message' => 'Invalid category'], 422);
            }
        }

        $transaction->update($validated);
        return response()->json($transaction->load(['account', 'category']));
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