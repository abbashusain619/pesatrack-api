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
        
        // Get display currency from query parameter, default to base currency
        $displayCurrency = $request->query('currency', $baseCurrency);

        $accounts = $user->accounts;
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('account')
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Step 1: Calculate net worth in base currency
        $netWorthInBase = 0;
        $conversionErrors = [];
        
        foreach ($accounts as $account) {
            $currency = $account->currency;
            $balance = $account->balance;

            if ($currency === $baseCurrency) {
                $netWorthInBase += $balance;
                continue;
            }

            $rateFrom = Currency::where('code', $currency)->value('rate_to_usd');
            $rateTo = Currency::where('code', $baseCurrency)->value('rate_to_usd');

            if (!$rateFrom || !$rateTo) {
                $conversionErrors[] = "Missing exchange rate for $currency or $baseCurrency";
                continue;
            }
            $netWorthInBase += $balance * ($rateFrom / $rateTo);
        }

        // Step 2: Convert net worth to display currency (if different from base)
       $netWorth = $netWorthInBase;
        if ($displayCurrency !== $baseCurrency) {
            $rateFrom = Currency::where('code', $baseCurrency)->value('rate_to_usd');
            $rateTo = Currency::where('code', $displayCurrency)->value('rate_to_usd');
            if ($rateFrom && $rateTo) {
                $netWorth = $netWorthInBase * ($rateFrom / $rateTo);  // FIXED
            } else {
                $conversionErrors[] = "Cannot convert to $displayCurrency (missing rate)";
            }
        }

        // Get all currencies for the dropdown
        $currencies = Currency::orderBy('code')->get();

        return view('dashboard', [
            'accounts' => $accounts,
            'recentTransactions' => $recentTransactions,
            'netWorth' => round($netWorth, 2),
            'baseCurrency' => $baseCurrency,
            'displayCurrency' => $displayCurrency,
            'currencies' => $currencies,
            'conversionErrors' => $conversionErrors,
        ]);
    }
}