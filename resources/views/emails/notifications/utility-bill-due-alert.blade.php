@extends('emails.layouts.base')

@section('preheader')
    @if($daysTillDue === 0)
        Your {{ $bill->utility_type }} bill is due today
    @else
        Your {{ $bill->utility_type }} bill is due in {{ $daysTillDue }} {{ Str::plural('day', $daysTillDue) }}
    @endif
@endsection

@section('content')
    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; color: #1B1B18; margin: 0 0 16px;" class="heading">Hello {{ $user->name }},</p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18; margin: 0 0 20px;" class="body-text">
        @if($daysTillDue === 0)
            Your <strong>{{ $bill->utility_type }}</strong> bill is due today.
        @else
            Your <strong>{{ $bill->utility_type }}</strong> bill is due in {{ $daysTillDue }} {{ Str::plural('day', $daysTillDue) }}.
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
        <!-- Budget notice -->
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 20px;">
            <tr>
                <td style="background-color: #F8F7F4; border-radius: 8px; border: 1px solid #E3E3E0; padding: 12px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #1B1B18;" class="highlight-bg body-text border-color">
                    <strong>Over budget:</strong> {{ $billAmountMkd }} exceeds your {{ $budgetThresholdMkd }} threshold.
                </td>
            </tr>
        </table>
    @endif

    @php
        $details = [
            'Utility' => $bill->utility_type,
        ];

        if ($bill->service_provider) {
            $details['Provider'] = $bill->service_provider;
        }

        $details['Amount'] = $billAmountMkd;
        $details['Due Date'] = $bill->due_date->format('F j, Y');
        $details['Period'] = $bill->bill_period_start->format('M j') . ' - ' . $bill->bill_period_end->format('M j, Y');

        if ($bill->account_number) {
            $details['Account'] = $bill->account_number;
        }

        if ($bill->usage_amount && $bill->usage_unit) {
            $details['Usage'] = $bill->usage_amount . ' ' . $bill->usage_unit;
        }

        $details['Payment'] = $bill->auto_pay_enabled ? 'Auto-pay' : 'Manual';
    @endphp

    <x-emails.components.detail-list :items="$details" />

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #706F6C; margin: 0 0 4px;" class="subtext">
        @if($bill->auto_pay_enabled)
            Auto-pay is enabled. Ensure sufficient funds are available.
        @elseif($daysTillDue === 0)
            This bill is due today.
        @else
            Manual payment required before the due date.
        @endif
    </p>

    <x-emails.components.button :url="url('/utility-bills/' . $bill->id)">
        View Bill
    </x-emails.components.button>
@endsection
