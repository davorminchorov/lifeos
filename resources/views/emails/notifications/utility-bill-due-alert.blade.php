@extends('emails.layouts.base')

@section('preheader')
    @if($daysTillDue === 0)
        Your {{ $bill->utility_type }} bill is due today
    @else
        Your {{ $bill->utility_type }} bill is due in {{ $daysTillDue }} {{ Str::plural('day', $daysTillDue) }}
    @endif
@endsection

@section('content')
    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 600; color: #1B1B18; margin: 0 0 20px;" class="heading">Hello {{ $user->name }},</p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
        @if($daysTillDue === 0)
            Your utility bill payment is due <strong>today</strong>. Don't forget to pay to avoid late fees.
        @else
            Your utility bill payment is due in <strong>{{ $daysTillDue }} {{ Str::plural('day', $daysTillDue) }}</strong>. Here are the details:
        @endif
    </p>

    @php
        $currencyService = app(\App\Services\CurrencyService::class);
        $billCurrency = $bill->currency ?? config('currency.default', 'MKD');
        $billAmountDefault = $currencyService->convertToDefault($bill->bill_amount, $billCurrency);
        $billAmountMkd = $currencyService->format($billAmountDefault);
        $budgetThresholdMkd = null;
        if (!is_null($bill->budget_alert_threshold)) {
            $thresholdDefault = $currencyService->convertToDefault($bill->budget_alert_threshold, $billCurrency);
            $budgetThresholdMkd = $currencyService->format($thresholdDefault);
        }
    @endphp

    @if($bill->is_over_budget ?? false)
        <!-- Warning highlight -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
            <tr>
                <td style="background-color: #FFFBEB; border-left: 4px solid #F59E0B; border-radius: 0 8px 8px 0; padding: 16px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="highlight-bg body-text">
                    <strong>Budget Alert:</strong> This bill of <strong>{{ $billAmountMkd }}</strong> exceeds your budget threshold of {{ $budgetThresholdMkd }}.
                </td>
            </tr>
        </table>
    @else
        <!-- Standard highlight -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
            <tr>
                <td style="background-color: #FEF2F2; border-left: 4px solid #F53003; border-radius: 0 8px 8px 0; padding: 16px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="highlight-bg body-text">
                    <strong>{{ $bill->utility_type }}</strong> bill of <strong>{{ $billAmountMkd }}</strong>
                    @if($daysTillDue === 0)
                        is due today
                    @else
                        due {{ $bill->due_date->format('F j, Y') }}
                    @endif
                </td>
            </tr>
        </table>
    @endif

    @php
        $details = [
            'Utility Type' => $bill->utility_type,
            'Bill Amount' => $billAmountMkd,
            'Due Date' => $bill->due_date->format('F j, Y'),
            'Billing Period' => $bill->bill_period_start->format('M j') . ' - ' . $bill->bill_period_end->format('M j, Y'),
        ];

        if ($bill->service_provider) {
            $details['Service Provider'] = $bill->service_provider;
        }

        if ($bill->account_number) {
            $details['Account Number'] = $bill->account_number;
        }

        if ($bill->usage_amount && $bill->usage_unit) {
            $details['Usage'] = $bill->usage_amount . ' ' . $bill->usage_unit;
        }

        $details['Payment Method'] = $bill->auto_pay_enabled ? 'Auto-pay enabled' : 'Manual payment required';
    @endphp

    <x-emails.components.detail-list :items="$details" />

    @if($bill->auto_pay_enabled)
        <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
            This bill has auto-pay enabled and should be processed automatically on the due date. Please ensure your payment method has sufficient funds.
        </p>
    @else
        @if($daysTillDue === 0)
            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
                <strong>Action Required:</strong> Your bill is due today. Pay immediately to avoid late fees and potential service interruption.
            </p>
        @else
            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
                <strong>Manual Payment Required:</strong> This bill needs to be paid manually. Make sure to pay before the due date to avoid late fees.
            </p>
        @endif
    @endif

    @if($bill->is_over_budget ?? false)
        <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 16px 0 8px;" class="body-text">
            Consider reviewing your usage patterns to manage costs:
        </p>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Check for energy-efficient settings or appliances</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Review usage during peak hours</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Contact your provider about budget billing options</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Set up usage alerts to monitor consumption</td></tr>
        </table>
    @endif

    <x-emails.components.button :url="url('/utility-bills/' . $bill->id)">
        View Bill Details
    </x-emails.components.button>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
        Keep track of all your utility bills and spending patterns with LifeOS.
    </p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0;" class="body-text">
        <strong>Best regards,</strong><br>
        The LifeOS Team
    </p>
@endsection
