<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class NetWorthController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $baseCurrency = $user->base_currency;

        $accounts = $user->accounts; // assuming relationship exists

        $total = 0;
        $conversionErrors = [];

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
                $conversionErrors[] = "Missing exchange rate for $currency or $baseCurrency";
                continue;
            }

            $converted = $balance * ($rateFrom / $rateTo);
            $total += $converted;
        }

        return response()->json([
            'net_worth' => round($total, 2),
            'currency' => $baseCurrency,
            'errors' => $conversionErrors,
        ]);
    }
}