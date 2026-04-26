<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function data(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'account_id' => 'nullable|exists:accounts,id',
            'category_id'=> 'nullable|exists:categories,id',
        ]);

        // 1) Spending by category (expenses only)
        $expenseQuery = Transaction::where('user_id', Auth::id())
            ->where('type', 'expense')
            ->with('category');

        if ($request->filled('start_date')) {
            $expenseQuery->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $expenseQuery->whereDate('transaction_date', '<=', $request->end_date);
        }
        if ($request->filled('account_id')) {
            $expenseQuery->where('account_id', $request->account_id);
        }
        if ($request->filled('category_id')) {
            $expenseQuery->where('category_id', $request->category_id);
        }

        $spendingByCategory = $expenseQuery
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category ? $item->category->name : 'Uncategorized',
                'total'    => (float) $item->total,
            ]);

        // 2) Income vs Expense over time (grouped by month)
        $start = $request->filled('start_date') ? $request->start_date : now()->subMonths(6)->startOfMonth();
        $end   = $request->filled('end_date') ? $request->end_date : now()->endOfMonth();

        $incomeExpense = Transaction::where('user_id', Auth::id())
            ->whereBetween('transaction_date', [$start, $end])
            ->when($request->filled('account_id'), fn($q) => $q->where('account_id', $request->account_id))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->get()
            ->groupBy(fn($tx) => $tx->transaction_date->format('Y-m'))
            ->map(fn($monthly) => [
                'income'  => $monthly->where('type', 'income')->sum('amount'),
                'expense' => $monthly->where('type', 'expense')->sum('amount'),
            ]);

        $labels = $incomeExpense->keys();
        $incomeData  = $incomeExpense->pluck('income')->values();
        $expenseData = $incomeExpense->pluck('expense')->values();

        return response()->json([
            'spending_by_category' => $spendingByCategory,
            'income_expense' => [
                'labels'  => $labels,
                'income'  => $incomeData,
                'expenses'=> $expenseData,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id())
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $transactions = $query->get();

        $fileName = 'transactions_' . now()->format('Ymd_His') . '.csv';
        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Account', 'Category', 'Type', 'Description', 'Reference', 'Amount']);
            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->transaction_date->format('Y-m-d'),
                    $tx->account->name,
                    $tx->category?->name ?? '',
                    ucfirst($tx->type),
                    $tx->description ?? '',
                    $tx->reference ?? '',
                    $tx->type === 'income' ? $tx->amount : -$tx->amount,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}