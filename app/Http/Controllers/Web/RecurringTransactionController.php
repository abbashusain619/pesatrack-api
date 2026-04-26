<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RecurringTransaction;
use App\Models\Account;
use App\Models\Category;
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
            ->orderBy('next_date')
            ->get();
        return view('recurring.index', compact('templates'));
    }

    public function create()
    {
        $accounts = Account::where('user_id', Auth::id())->get();
        $categories = Category::forUser(Auth::id())->orderBy('name')->get();
        return view('recurring.create', compact('accounts', 'categories'));
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

        RecurringTransaction::create($validated);

        return redirect()->route('recurring.index')->with('success', 'Recurring template created.');
    }

    public function edit(RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) abort(403);
        $accounts = Account::where('user_id', Auth::id())->get();
        $categories = Category::forUser(Auth::id())->orderBy('name')->get();
        return view('recurring.edit', compact('recurring', 'accounts', 'categories'));
    }

    public function update(Request $request, RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) abort(403);

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
            'is_active' => 'boolean',
        ]);

        $recurring->update($validated);
        return redirect()->route('recurring.index')->with('success', 'Template updated.');
    }

    public function destroy(RecurringTransaction $recurring)
    {
        if ($recurring->user_id !== Auth::id()) abort(403);
        $recurring->delete();
        return redirect()->route('recurring.index')->with('success', 'Template deleted.');
    }

    // Pending transactions list and actions
    public function pending()
    {
        $pending = Transaction::where('user_id', Auth::id()) //line 95
            ->where('status', 'pending')
            ->with(['account', 'category'])
            ->orderBy('transaction_date')
            ->get();
        return view('recurring.pending', compact('pending'));
    }

    public function confirm(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id() || $transaction->status !== 'pending') {
            abort(403);
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
            return redirect()->back()->with('success', 'Transaction confirmed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to confirm transaction.');
        }
    }

    public function reject(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id() || $transaction->status !== 'pending') {
            abort(403);
        }
        $transaction->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Transaction rejected.');
    }
}