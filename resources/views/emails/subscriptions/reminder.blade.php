@component('mail::message')
# Subscription Payment Reminder

Your subscription payment for **{{ $subscriptionName }}** is due in **{{ $daysUntil }}**.

## Payment Details:
- **Due Date:** {{ \Carbon\Carbon::parse($paymentDate)->format('F j, Y') }}
- **Amount:** {{ $currency }} {{ number_format($amount, 2) }}

Don't miss your payment to ensure uninterrupted service.

@component('mail::button', ['url' => config('app.url') . '/subscriptions'])
View Subscription
@endcomponent

Thank you for using LifeOS to manage your subscriptions.

Regards,<br>
{{ config('app.name') }}
@endcomponent
