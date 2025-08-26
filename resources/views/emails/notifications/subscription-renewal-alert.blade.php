@extends('emails.layouts.base')

@section('content')
    <div class="greeting">Hello {{ $user->name }}!</div>

    <div class="content-text">
        @if($daysUntilRenewal === 0)
            Your subscription is renewing <strong>today</strong>! We wanted to give you a heads up about the upcoming charge.
        @else
            Your subscription is set to renew in <strong>{{ $daysUntilRenewal }} {{ Str::plural('day', $daysUntilRenewal) }}</strong>. Here are the details:
        @endif
    </div>

    <div class="highlight">
        <strong>{{ $subscription->service_name }}</strong> subscription
        @if($daysUntilRenewal === 0)
            renews today
        @else
            renews {{ $subscription->next_billing_date->format('F j, Y') }}
        @endif
    </div>

    @php
        $currencyService = app(\App\Services\CurrencyService::class);
        $subscriptionCurrency = $subscription->currency ?? config('currency.default', 'MKD');
        $costInDefault = $currencyService->convertToDefault($subscription->cost, $subscriptionCurrency);
        $formattedCost = $currencyService->format($costInDefault);

        $details = [
            'Service' => $subscription->service_name,
            'Cost' => $formattedCost,
            'Next Billing Date' => $subscription->next_billing_date->format('F j, Y'),
        ];

        if ($subscription->payment_method) {
            $details['Payment Method'] = $subscription->payment_method;
        }

        $details['Auto Renewal'] = $subscription->auto_renewal ? 'Enabled' : 'Manual renewal required';
    @endphp

    <x-emails.components.detail-list :items="$details" />

    @if($subscription->auto_renewal)
        <div class="content-text">
            This subscription will automatically renew using your saved payment method. No action is required from you.
        </div>
    @else
        <div class="content-text">
            <strong>Action Required:</strong> This subscription requires manual renewal. Please review and update your subscription settings if you wish to continue.
        </div>
    @endif

    <x-emails.components.button :url="url('/subscriptions/' . $subscription->id)">
        Manage Subscription
    </x-emails.components.button>

    <div class="content-text">
        You can cancel or modify this subscription anytime from your dashboard. If you have any questions, feel free to reach out to our support team.
    </div>

    <div class="content-text">
        <strong>Best regards,</strong><br>
        The LifeOS Team
    </div>
@endsection
