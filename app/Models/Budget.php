<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;
use App\Models\Currency;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category_id', 'amount', 'period', 'start_date', 'end_date', 'alert_threshold'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Scope for active budget for a given date (optional)
    public function scopeActiveForDate($query, $date = null)
    {
        $date = $date ?: now();
        if (!($date instanceof \Carbon\Carbon)) {
            $date = \Carbon\Carbon::parse($date);
        }
        return $query->where(function ($q) use ($date) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
        });
    }

    public function spentAmount($date = null)
    {
        $date = $date ?? now();
        $start = $this->start_date ?? $this->getPeriodStart($date);
        $end = $this->end_date ?? $this->getPeriodEnd($date);
        $user = $this->user;
        $baseCurrency = $user->base_currency ?? 'USD';

        $transactions = Transaction::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->with('account')
            ->get();

        $total = 0;
        foreach ($transactions as $tx) {
            $account = $tx->account;
            $txCurrency = $account->currency;
            if ($txCurrency === $baseCurrency) {
                $total += $tx->amount;
            } else {
                // Convert to base currency using latest rates
                $rateFrom = Currency::where('code', $txCurrency)->value('rate_to_usd');
                $rateTo   = Currency::where('code', $baseCurrency)->value('rate_to_usd');
                if ($rateFrom && $rateTo) {
                    $converted = $tx->amount * ($rateFrom / $rateTo);
                    $total += $converted;
                } else {
                    $total += $tx->amount; // fallback
                }
            }
        }
        return $total;
    }

    private function getPeriodStart($date)
    {
        switch ($this->period) {
            case 'monthly': return $date->copy()->startOfMonth();
            case 'weekly': return $date->copy()->startOfWeek();
            case 'yearly': return $date->copy()->startOfYear();
            default: return $date->copy()->startOfMonth();
        }
    }

    private function getPeriodEnd($date)
    {
        switch ($this->period) {
            case 'monthly': return $date->copy()->endOfMonth();
            case 'weekly': return $date->copy()->endOfWeek();
            case 'yearly': return $date->copy()->endOfYear();
            default: return $date->copy()->endOfMonth();
        }
    }
}