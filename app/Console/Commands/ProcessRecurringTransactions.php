<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'recurring:process';
    protected $description = 'Generate pending transactions from recurring templates';
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_date' => 'date',
    ];

    public function handle()
    {
        $today = Carbon::today();
        $templates = RecurringTransaction::where('is_active', true)
            ->where('next_date', '<=', $today)
            ->get();

        foreach ($templates as $template) {
            DB::transaction(function () use ($template, $today) {
                // Create pending transaction
                Transaction::create([
                    'user_id' => $template->user_id,
                    'account_id' => $template->account_id,
                    'category_id' => $template->category_id,
                    'type' => $template->type,
                    'amount' => $template->amount,
                    'description' => $template->description,
                    'reference' => $template->reference,
                    'transaction_date' => $today,
                    'is_synced' => false,
                    'status' => 'pending',
                ]);

                // Calculate next date
                $next = $template->next_date->copy();
                switch ($template->frequency) {
                    case 'daily':
                        $next->addDays($template->interval_count);
                        break;
                    case 'weekly':
                        $next->addWeeks($template->interval_count);
                        break;
                    case 'monthly':
                        $next->addMonths($template->interval_count);
                        break;
                    case 'yearly':
                        $next->addYears($template->interval_count);
                        break;
                }
                // If end_date is set and next > end_date, deactivate or stop.
                if ($template->end_date && $next->gt($template->end_date)) {
                    $template->update(['is_active' => false]);
                } else {
                    $template->update(['next_date' => $next]);
                }
            });
        }

        $this->info('Recurring transactions processed.');
    }
}