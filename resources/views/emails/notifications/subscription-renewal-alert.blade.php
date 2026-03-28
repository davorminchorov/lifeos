@extends('emails.layouts.base')

@section('preheader')
    @if($daysUntilRenewal === 0)
        Your {{ $subscription->service_name }} subscription renews today
    @else
        Your {{ $subscription->service_name }} subscription renews in {{ $daysUntilRenewal }} {{ Str::plural('day', $daysUntilRenewal) }}
    @endif
@endsection

@section('content')
    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 600; color: #1B1B18; margin: 0 0 20px;" class="heading">Hello {{ $user->name }},</p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
        @if($daysUntilRenewal === 0)
            Your subscription is renewing <strong>today</strong>. We wanted to give you a heads up about the upcoming charge.
        @else
            Your subscription is set to renew in <strong>{{ $daysUntilRenewal }} {{ Str::plural('day', $daysUntilRenewal) }}</strong>. Here are the details:
        @endif
    </p>

    <!-- Highlight card -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
        <tr>
            <td style="background-color: #FEF2F2; border-left: 4px solid #F53003; border-radius: 0 8px 8px 0; padding: 16px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="highlight-bg body-text">
                <strong>{{ $subscription->service_name }}</strong> subscription
                @if($daysUntilRenewal === 0)
                    renews today
                @else
                    renews {{ $subscription->next_billing_date->format('F j, Y') }}
                @endif
            </td>
        </tr>
    </table>

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
        <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
            This subscription will automatically renew using your saved payment method. No action is required from you.
        </p>
    @else
        <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
            <strong>Action Required:</strong> This subscription requires manual renewal. Please review and update your subscription settings if you wish to continue.
        </p>
    @endif

    <x-emails.components.button :url="url('/subscriptions/' . $subscription->id)">
        Manage Subscription
    </x-emails.components.button>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
        You can cancel or modify this subscription anytime from your dashboard.
    </p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0;" class="body-text">
        <strong>Best regards,</strong><br>
        The LifeOS Team
    </p>
@endsection
