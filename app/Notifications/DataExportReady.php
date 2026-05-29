<?php

namespace App\Notifications;

use App\Models\DataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DataExportReady extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DataExport $export) {}

    public function via(object $notifiable): array
    {
        return $notifiable->getEnabledNotificationChannels('data_export');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your data export is ready')
            ->line('Your export ('.$this->describeModules().') has finished and is ready to download.')
            ->action('Download export', route('exports.index'))
            ->line('This export will be available until '.$this->export->expires_at?->toDayDateTimeString().'.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'export_id' => $this->export->id,
            'file_name' => $this->export->file_name,
            'modules' => $this->export->modules,
            'row_counts' => $this->export->row_counts,
            'status' => $this->export->status,
        ];
    }

    private function describeModules(): string
    {
        $count = count($this->export->modules);

        return $count === 1
            ? str_replace('-', ' ', $this->export->modules[0])
            : $count.' modules';
    }
}
