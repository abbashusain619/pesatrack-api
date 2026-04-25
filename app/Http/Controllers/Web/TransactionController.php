<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::where('user_id', auth()->id())
            ->with(['account', 'category']);

        // Apply filters
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('category_id')) {
            if ($request->category_id === 'null') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $request->category_id);
            }
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                ->orWhere('reference', 'like', $search);
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(20);

        // For filter dropdowns
        $accounts = Account::where('user_id', auth()->id())->get();
        $categories = Category::forUser(auth()->id())->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'accounts', 'categories'));
    }

    public function create()
    {
        $accounts = Account::where('user_id', auth()->id())->get();
        $categories = Category::forUser(auth()->id())->orderBy('name')->get();
        return view('transactions.create', compact('accounts', 'categories'));
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
            'transaction_date' => 'nullable|date',
        ]);

        $account = Account::findOrFail($validated['account_id']);

        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        // Optional category ownership check
        if (!empty($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
            if ($category && $category->user_id && $category->user_id !== auth()->id()) {
                return back()->withErrors(['category_id' => 'Invalid category']);
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

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }
        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }
        $accounts = Account::where('user_id', auth()->id())->get();
        $categories = Category::forUser(auth()->id())->orderBy('name')->get();
        return view('transactions.edit', compact('transaction', 'accounts', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        \Log::info('=== Transaction update started ===', ['transaction_id' => $transaction->id]);

        // Synced check...
        if ($transaction->is_synced) {
            // ... (keep as before)
        }

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'transaction_date' => 'nullable|date',
        ]);

        $newAccount = Account::findOrFail($validated['account_id']);
        if ($newAccount->user_id !== auth()->id()) {
            return back()->withErrors(['account_id' => 'Invalid account']);
        }

        \Log::info('Old transaction data', [
            'old_account_id' => $transaction->account_id,
            'old_type' => $transaction->type,
            'old_amount' => $transaction->amount,
        ]);

        \Log::info('New transaction data', [
            'new_account_id' => $validated['account_id'],
            'new_type' => $validated['type'],
            'new_amount' => $validated['amount'],
        ]);

        DB::beginTransaction();
        try {
            // Get old account fresh
            $oldAccount = Account::find($transaction->account_id);
            if (!$oldAccount) {
                throw new \Exception('Old account not found');
            }

            \Log::info('Old account before reversal', [
                'account_id' => $oldAccount->id,
                'balance_before' => $oldAccount->balance,
            ]);

            // Reverse old effect
            if ($transaction->type === 'income') {
                $oldAccount->balance -= $transaction->amount;
                \Log::info('Reversing income', ['deduct' => $transaction->amount]);
            } else {
                $oldAccount->balance += $transaction->amount;
                \Log::info('Reversing expense', ['add' => $transaction->amount]);
            }
            $oldAccount->save();

            \Log::info('Old account after reversal', ['balance_after' => $oldAccount->balance]);

            // New account (might be same as old)
            $newAccountReloaded = Account::find($validated['account_id']);
            \Log::info('New account before applying', [
                'account_id' => $newAccountReloaded->id,
                'balance_before' => $newAccountReloaded->balance,
            ]);

            // Apply new effect
            if ($validated['type'] === 'income') {
                $newAccountReloaded->balance += $validated['amount'];
                \Log::info('Applying income', ['add' => $validated['amount']]);
            } else {
                $newAccountReloaded->balance -= $validated['amount'];
                \Log::info('Applying expense', ['deduct' => $validated['amount']]);
            }
            $newAccountReloaded->save();

            \Log::info('New account after applying', ['balance_after' => $newAccountReloaded->balance]);

            // Update transaction
            $transaction->update([
                'account_id' => $validated['account_id'],
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'category_id' => $validated['category_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'transaction_date' => $validated['transaction_date'] ?? now(),
            ]);

            DB::commit();
            \Log::info('Transaction update successful');
            return redirect()->route('transactions.index')->with('success', 'Transaction updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Update failed: ' . $e->getMessage());
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