@extends('emails.layouts.base')

@section('preheader')
    @if($daysUntilRenewal === 0)
        Your {{ $subscription->service_name }} subscription renews today
    @else
        Your {{ $subscription->service_name }} subscription renews in {{ $daysUntilRenewal }} {{ Str::plural('day', $daysUntilRenewal) }}
    @endif
@endsection

@section('content')
    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; color: #1B1B18; margin: 0 0 16px;" class="heading">Hello {{ $user->name }},</p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18; margin: 0 0 20px;" class="body-text">
        @if($daysUntilRenewal === 0)
            Your <strong>{{ $subscription->service_name }}</strong> subscription renews today.
        @else
            Your <strong>{{ $subscription->service_name }}</strong> subscription renews in {{ $daysUntilRenewal }} {{ Str::plural('day', $daysUntilRenewal) }}.
        @endif
    </p>

    @php
        $currencyService = app(\App\Services\CurrencyService::class);
        $subscriptionCurrency = $subscription->currency ?? config('currency.default', 'MKD');
        $costInDefault = $currencyService->convertToDefault($subscription->cost, $subscriptionCurrency);
        $formattedCost = $currencyService->format($costInDefault);

        $details = [
            'Service' => $subscription->service_name,
            'Cost' => $formattedCost,
            'Next Billing' => $subscription->next_billing_date->format('F j, Y'),
        ];

        if ($subscription->payment_method) {
            $details['Payment Method'] = $subscription->payment_method;
        }

        $details['Auto Renewal'] = $subscription->auto_renewal ? 'Enabled' : 'Manual';
    @endphp

    <x-emails.components.detail-list :items="$details" />

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #706F6C; margin: 0 0 4px;" class="subtext">
        @if($subscription->auto_renewal)
            This subscription renews automatically. No action needed.
        @else
            This subscription requires manual renewal.
        @endif
    </p>

    <x-emails.components.button :url="url('/subscriptions/' . $subscription->id)">
        View Subscription
    </x-emails.components.button>
@endsection
