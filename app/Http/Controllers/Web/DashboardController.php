<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $baseCurrency = $user->base_currency ?? 'USD';

        $accounts = $user->accounts;
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('account')
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Net worth calculation
        $total = 0;
        $errors = [];
        foreach ($accounts as $account) {
            $currency = $account->currency;
            $balance = $account->balance;

            if ($currency === $baseCurrency) {
                $total += $balance;
                continue;
            }

            $rateFrom = Currency::where('code', $currency)->value('rate_to_usd');
            $rateTo = Currency::where('code', $baseCurrency)->value('rate_to_usd');

            if (!$rateFrom || !$rateTo) {
                $errors[] = "Missing exchange rate for $currency or $baseCurrency";
                continue;
            }
            $total += $balance * ($rateFrom / $rateTo);
        }

        return view('dashboard', [
            'accounts' => $accounts,
            'recentTransactions' => $recentTransactions,
            'netWorth' => round($total, 2),
            'baseCurrency' => $baseCurrency,
            'conversionErrors' => $errors,
        ]);
    }
}