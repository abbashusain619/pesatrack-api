<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetAlertNotification extends Notification
{
    use Queueable;

    protected $budget;
    protected $percentage;

    public function __construct($budget, $percentage)
    {
        $this->budget = $budget;
        $this->percentage = $percentage;
    }

    public function via($notifiable)
    {
        return ['database']; // store in DB, can add 'mail' later
    }

    public function toDatabase($notifiable)
    {
        return [
            'budget_id' => $this->budget->id,
            'category_name' => $this->budget->category->name,
            'budget_amount' => $this->budget->amount,
            'spent' => $this->budget->spent ?? 0,
            'percentage' => round($this->percentage, 1),
            'message' => sprintf(
                'You have used %.1f%% of your budget for "%s". Budget: %s, Spent: %s',
                $this->percentage,
                $this->budget->category->name,
                number_format($this->budget->amount, 2),
                number_format($this->budget->spent, 2)
            ),
        ];
    }

    // optional: you can also add toArray for API responses
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}