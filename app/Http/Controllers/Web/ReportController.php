<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Pass filter options for the dropdowns
        $accounts = Account::where('user_id', Auth::id())->get();
        $categories = Category::forUser(Auth::id())->orderBy('name')->get();

        return view('reports.index', compact('accounts', 'categories'));
    }

    // API endpoint for chart data (called via AJAX)
    public function data(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'account_id' => 'nullable|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $query = Transaction::where('user_id', Auth::id())
            ->where('type', 'expense'); // Expenses only for spending pie

        // Apply filters
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

        // Category spending data
        $spendingByCategory = $query->select('category_id', \DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category ? $item->category->name : 'Uncategorized',
                    'total' => (float) $item->total,
                ];
            });

        // Income vs Expense over time (grouped by month)
        $start = $request->filled('start_date') ? $request->start_date : now()->subMonths(6)->startOfMonth();
        $end = $request->filled('end_date') ? $request->end_date : now()->endOfMonth();

        $incomeExpense = Transaction::where('user_id', Auth::id())
            ->whereBetween('transaction_date', [$start, $end])
            ->when($request->filled('account_id'), function ($q) use ($request) {
                return $q->where('account_id', $request->account_id);
            })
            ->when($request->filled('category_id'), function ($q) use ($request) {
                return $q->where('category_id', $request->category_id);
            })
            ->get()
            ->groupBy(function ($tx) {
                return $tx->transaction_date->format('Y-m');
            })
            ->map(function ($monthlyTxs) {
                return [
                    'income' => $monthlyTxs->where('type', 'income')->sum('amount'),
                    'expense' => $monthlyTxs->where('type', 'expense')->sum('amount'),
                ];
            });

        // Prepare for Chart.js
        $labels = $incomeExpense->keys();
        $incomeData = $incomeExpense->pluck('income')->values();
        $expenseData = $incomeExpense->pluck('expense')->values();

        return response()->json([
            'spending_by_category' => $spendingByCategory,
            'income_expense' => [
                'labels' => $labels,
                'income' => $incomeData,
                'expenses' => $expenseData,
            ],
        ]);
    }

    public function networthData(Request $request)
    {
        $user = auth()->user();
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->subMonths(11)->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now()->endOfMonth();

        // Generate monthly points (last day of each month in range)
        $months = [];
        $current = $startDate->copy()->startOfMonth();
        while ($current <= $endDate) {
            $months[] = $current->copy()->endOfMonth();
            $current->addMonth();
        }

        $netWorthData = [];
        foreach ($months as $monthEnd) {
            // For each account, compute balance at month-end via transactions
            $total = DB::table('accounts')
                ->where('user_id', $user->id)
                ->selectRaw('
                    (
                        COALESCE((
                            SELECT SUM(
                                CASE WHEN t.type = "income" THEN t.amount ELSE -t.amount END
                            ) FROM transactions t
                            WHERE t.account_id = accounts.id
                            AND t.transaction_date <= ?
                        ), 0) + accounts.balance
                    ) as balance', [$monthEnd])
                ->get()
                ->sum('balance'); // sum over all accounts

            $netWorthData[] = [
                'date' => $monthEnd->format('Y-m-d'),
                'net_worth' => round($total, 2),
            ];
        }

        return response()->json([
            'labels' => collect($netWorthData)->pluck('date'),
            'data' => collect($netWorthData)->pluck('net_worth'),
        ]);
    }

    public function export(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id())
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc');

        // Apply same filters as reports
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

        $filename = 'transactions_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Account', 'Category', 'Type', 'Description', 'Reference', 'Amount']);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->transaction_date->format('Y-m-d'),
                    $tx->account->name,
                    $tx->category ? $tx->category->name : '',
                    ucfirst($tx->type),
                    $tx->description ?? '',
                    $tx->reference ?? '',
                    $tx->type == 'income' ? $tx->amount : -$tx->amount,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}