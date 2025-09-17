<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetThresholdAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Budget $budget, public string $direction) {}

    public function via(object $notifiable): array
    {
        // Channels will be overridden by listener via($channels), but keep sensible default
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->budget->getStatus();
        $util = $this->budget->getUtilizationPercentage();

        return (new MailMessage)
            ->subject('Budget alert: '.ucfirst(str_replace('_', ' ', $status)))
            ->line("Your {$this->budget->category} budget is at {$util}% utilization.")
            ->line(match ($status) {
                'exceeded' => 'You have exceeded your budget for this period.',
                'warning' => 'You are nearing your budget limit.',
                default => 'Budget status update.',
            })
            ->line('Period: '.$this->budget->start_date->toDateString().' to '.$this->budget->end_date->toDateString());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'budget_id' => $this->budget->id,
            'category' => $this->budget->category,
            'status' => $this->budget->getStatus(),
            'utilization' => $this->budget->getUtilizationPercentage(),
            'direction' => $this->direction,
            'period' => [
                'start' => optional($this->budget->start_date)->toDateString(),
                'end' => optional($this->budget->end_date)->toDateString(),
            ],
        ];
    }
}
