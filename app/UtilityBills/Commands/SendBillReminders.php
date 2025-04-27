<?php

namespace App\UtilityBills\Commands;

use App\UtilityBills\Domain\UtilityBill;
use App\UtilityBills\Projections\ReminderList;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBillReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utility-bills:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send due reminders for utility bills';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $dueReminders = ReminderList::where('status', 'scheduled')
            ->where('reminder_date', $today)
            ->get();

        $count = 0;

        foreach ($dueReminders as $reminder) {
            try {
                // Get the aggregate
                $billAggregate = UtilityBill::retrieve($reminder->bill_id);

                // Send the reminder
                $billAggregate->sendReminder(
                    Carbon::now()->toIso8601String(),
                    $reminder->message
                );

                // Persist the changes
                $billAggregate->persist();

                // Increment count
                $count++;

                $this->info("Sent reminder for bill {$reminder->bill_id}");
            } catch (\Exception $e) {
                Log::error("Failed to send reminder for bill {$reminder->bill_id}: {$e->getMessage()}");
                $this->error("Failed to send reminder for bill {$reminder->bill_id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$count} reminders");

        return 0;
    }
}
