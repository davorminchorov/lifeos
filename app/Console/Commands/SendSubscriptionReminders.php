<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class SendSubscriptionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for upcoming subscription payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->format('Y-m-d');

        // Get all reminders due today that haven't been sent
        $reminders = DB::table('subscription_reminders')
            ->where('reminder_date', $today)
            ->where('sent', false)
            ->get();

        if ($reminders->isEmpty()) {
            $this->info('No reminders to send today.');
            return 0;
        }

        $this->info("Found {$reminders->count()} reminders to send.");

        foreach ($reminders as $reminder) {
            $this->sendReminder($reminder);

            // Mark the reminder as sent
            DB::table('subscription_reminders')
                ->where('subscription_id', $reminder->subscription_id)
                ->update([
                    'sent' => true,
                    'sent_at' => Carbon::now(),
                ]);

            $this->info("Sent reminder for subscription: {$reminder->subscription_name}");
        }

        return 0;
    }

    /**
     * Send the reminder notification based on the configured method.
     */
    private function sendReminder($reminder)
    {
        switch ($reminder->method) {
            case 'email':
                $this->sendEmailReminder($reminder);
                break;

            case 'sms':
                $this->sendSmsReminder($reminder);
                break;

            case 'push':
                $this->sendPushReminder($reminder);
                break;

            case 'in_app':
                $this->createInAppReminder($reminder);
                break;

            default:
                Log::warning("Unknown reminder method: {$reminder->method}");
                break;
        }
    }

    /**
     * Send an email reminder.
     */
    private function sendEmailReminder($reminder)
    {
        // In a real application, you would get the user's email from the database
        // and send an actual email using Laravel's Mail facade

        // For demonstration purposes, we'll just log the email
        Log::info("Sending email reminder for subscription: {$reminder->subscription_name}", [
            'subscription_id' => $reminder->subscription_id,
            'payment_date' => $reminder->payment_date,
            'amount' => $reminder->amount,
            'currency' => $reminder->currency,
        ]);

        // In a production environment, uncomment this code and set the correct user email
        // $user = DB::table('users')->first(); // Get the user from the database
        // if ($user && $user->email) {
        //     Mail::to($user->email)->send(new \App\Mail\SubscriptionReminderMail($reminder));
        // }
    }

    /**
     * Send an SMS reminder.
     */
    private function sendSmsReminder($reminder)
    {
        // In a real application, you would integrate with an SMS service

        Log::info("Sending SMS reminder for subscription: {$reminder->subscription_name}", [
            'subscription_id' => $reminder->subscription_id,
            'payment_date' => $reminder->payment_date,
            'amount' => $reminder->amount,
            'currency' => $reminder->currency,
        ]);
    }

    /**
     * Send a push notification reminder.
     */
    private function sendPushReminder($reminder)
    {
        // In a real application, you would integrate with a push notification service

        Log::info("Sending push notification reminder for subscription: {$reminder->subscription_name}", [
            'subscription_id' => $reminder->subscription_id,
            'payment_date' => $reminder->payment_date,
            'amount' => $reminder->amount,
            'currency' => $reminder->currency,
        ]);
    }

    /**
     * Create an in-app notification.
     */
    private function createInAppReminder($reminder)
    {
        // Format the payment date
        $formattedDate = Carbon::parse($reminder->payment_date)->format('F j, Y');
        $formattedAmount = number_format($reminder->amount, 2);

        // Create a new notification in the database
        DB::table('subscription_notifications')->insert([
            'id' => Uuid::uuid4()->toString(),
            'subscription_id' => $reminder->subscription_id,
            'title' => "Payment Reminder: {$reminder->subscription_name}",
            'message' => "Your subscription payment of {$reminder->currency} {$formattedAmount} for {$reminder->subscription_name} is due on {$formattedDate}.",
            'type' => 'reminder',
            'read' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Log::info("Created in-app notification for subscription: {$reminder->subscription_name}", [
            'subscription_id' => $reminder->subscription_id,
            'payment_date' => $reminder->payment_date,
            'amount' => $reminder->amount,
            'currency' => $reminder->currency,
        ]);
    }
}
