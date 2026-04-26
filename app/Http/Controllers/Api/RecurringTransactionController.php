<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecurringTransactionController extends Controller
{
    public function index()
    {
        $templates = RecurringTransaction::where('user_id', Auth::id())
            ->with(['account', 'category'])
            ->get();
        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'interval_count' => 'integer|min:1',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['next_date'] = $validated['start_date'];
        $validated['is_active'] = true;

        $template = RecurringTransaction::create($validated);
        return response()->json($template, 201);
    }

    public function show(RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) return response()->json(['message' => 'Unauthorized'], 403);
        return response()->json($recurring->load(['account', 'category']));
    }

    public function update(Request $request, RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) return response()->json(['message' => 'Unauthorized'], 403);

        $validated = $request->validate([
            'account_id' => 'sometimes|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'sometimes|in:income,expense',
            'amount' => 'sometimes|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'frequency' => 'sometimes|in:daily,weekly,monthly,yearly',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'interval_count' => 'sometimes|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $recurring->update($validated);
        return response()->json($recurring);
    }

    public function destroy(RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) return response()->json(['message' => 'Unauthorized'], 403);
        $recurring->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // Pending transactions list & actions for API
    public function pending()
    {
        $pending = Transaction::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with(['account', 'category'])
            ->get();
        return response()->json($pending);
    }

    public function confirm(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id() || $transaction->status !== 'pending') {
            return response()->json(['message' => 'Unauthorized or not pending'], 403);
        }

        DB::beginTransaction();
        try {
            $account = $transaction->account;
            if ($transaction->type === 'income') {
                $account->balance += $transaction->amount;
            } else {
                $account->balance -= $transaction->amount;
            }
            $account->save();
            $transaction->update(['status' => 'completed']);
            DB::commit();
            return response()->json(['message' => 'Transaction confirmed']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Confirmation failed'], 500);
        }
    }

    public function reject(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id() || $transaction->status !== 'pending') {
            return response()->json(['message' => 'Unauthorized or not pending'], 403);
        }
        $transaction->update(['status' => 'rejected']);
        return response()->json(['message' => 'Transaction rejected']);
    }
}