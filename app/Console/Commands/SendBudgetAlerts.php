<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Models\User;
use App\Notifications\BudgetAlertNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBudgetAlerts extends Command
{
    protected $signature = 'budgets:send-alerts';
    protected $description = 'Check all active budgets and send alerts if threshold exceeded';

    public function handle()
    {
        $users = User::whereHas('budgets')->get();

        foreach ($users as $user) {
            $today = Carbon::today();
            $budgets = Budget::where('user_id', $user->id)
                ->activeForDate($today)
                ->get();

            foreach ($budgets as $budget) {
                $spent = $budget->spentAmount($today);
                $budget->spent = $spent;   // ✅ Set spent attribute
                $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
                $threshold = $budget->alert_threshold ?? 80;

                if ($percentage >= $threshold) {
                    // Avoid spamming: check if a notification was sent in last 30 days
                    $recent = $user->notifications()
                        ->where('type', 'App\Notifications\BudgetAlertNotification')
                        ->where('data->budget_id', $budget->id)
                        ->where('created_at', '>=', Carbon::now()->subDays(30))
                        ->exists();

                    if (!$recent) {
                        $user->notify(new BudgetAlertNotification($budget, $percentage));
                        $this->info("Alert sent to {$user->email} for budget: {$budget->category->name}");
                    }
                }
            }
        }

        $this->info('Budget alert scan complete.');
    }
}