<?php

namespace App\Traits;

use App\Models\Budget;
use App\Models\Transaction;
use App\Notifications\BudgetAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait ChecksBudgetAlerts
{
    protected function checkBudgetsAfterTransaction(Transaction $transaction)
    {
        // Only expenses affect budgets
        if ($transaction->type !== 'expense') return;

        $user = $transaction->user;
        $today = $transaction->transaction_date ?? now();

        // Find active budgets for the category of this transaction
        $budgets = Budget::where('user_id', $user->id)
            ->where('category_id', $transaction->category_id)
            ->activeForDate($today)
            ->get();

        foreach ($budgets as $budget) {
            // Recalculate spent amount in user's base currency
            $spent = $budget->spentAmount($today);
            $budget->spent = $spent;
            $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;

            // Check threshold (default 80 if not set)
            $threshold = $budget->alert_threshold ?? 80;
            if ($percentage >= $threshold) {
                // Optionally check if recently notified (within last 30 days) to avoid spam
                $recentNotify = $user->notifications()
                    ->where('type', 'App\Notifications\BudgetAlertNotification')
                    ->where('data->budget_id', $budget->id)
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->exists();
                if (!$recentNotify) {
                    $user->notify(new BudgetAlertNotification($budget, $percentage));
                }
            }
        }
    }
}