@extends('emails.layouts.base')

@section('content')
    <div class="greeting">Hello {{ $user->name }}!</div>

    <div class="content-text">
        @if($daysTillDue === 0)
            Your utility bill payment is due <strong>today</strong>! Don't forget to pay to avoid late fees.
        @else
            Your utility bill payment is due in <strong>{{ $daysTillDue }} {{ Str::plural('day', $daysTillDue) }}</strong>. Here are the details:
        @endif
    </div>

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
        <div class="highlight" style="border-left-color: #F59E0B; background-color: #FFFBEB;">
            <strong>⚠️ Budget Alert:</strong> This bill of <strong>{{ $billAmountMkd }}</strong> exceeds your budget threshold of {{ $budgetThresholdMkd }}.
        </div>
    @else
        <div class="highlight">
            <strong>{{ $bill->utility_type }}</strong> bill of <strong>{{ $billAmountMkd }}</strong>
            @if($daysTillDue === 0)
                is due today
            @else
                due {{ $bill->due_date->format('F j, Y') }}
            @endif
        </div>
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
        <div class="content-text">
            This bill has auto-pay enabled and should be processed automatically on the due date. Please ensure your payment method has sufficient funds available.
        </div>
    @else
        @if($daysTillDue === 0)
            <div class="content-text">
                <strong>Action Required:</strong> Your bill is due today! Pay immediately to avoid late fees and potential service interruption.
            </div>
        @else
            <div class="content-text">
                <strong>Manual Payment Required:</strong> This bill needs to be paid manually. Make sure to pay before the due date to avoid late fees.
            </div>
        @endif
    @endif

    @if($bill->is_over_budget ?? false)
        <div class="content-text">
            Consider reviewing your usage patterns and explore energy-saving options to help manage your utility costs:
        </div>
        <div class="content-text">
            • Check for energy-efficient settings or appliances<br>
            • Review usage during peak hours<br>
            • Contact your service provider about budget billing options<br>
            • Set up usage alerts to monitor consumption
        </div>
    @endif

    <x-emails.components.button :url="url('/utility-bills/' . $bill->id)">
        View Bill Details
    </x-emails.components.button>

    <div class="content-text">
        Keep track of all your utility bills and spending patterns with LifeOS. Set up budget alerts and payment reminders to stay on top of your household expenses.
    </div>

    <div class="content-text">
        <strong>Best regards,</strong><br>
        The LifeOS Team
    </div>
@endsection
