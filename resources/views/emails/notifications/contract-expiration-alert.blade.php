@extends('emails.layouts.base')

@section('preheader')
    @if($isNoticeAlert)
        @if($daysUntilExpiration === 0)
            Contract notice period deadline for {{ $contract->title }} is today
        @else
            Contract notice period for {{ $contract->title }} ends in {{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}
        @endif
    @else
        @if($daysUntilExpiration === 0)
            Your {{ $contract->title }} contract expires today
        @else
            Your {{ $contract->title }} contract expires in {{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}
        @endif
    @endif
@endsection

@section('content')
    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; color: #1B1B18; margin: 0 0 16px;" class="heading">Hello {{ $user->name }},</p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18; margin: 0 0 20px;" class="body-text">
        @if($isNoticeAlert)
            @if($daysUntilExpiration === 0)
                The notice period deadline for <strong>{{ $contract->title }}</strong> is today. This is your last opportunity to provide termination notice.
            @else
                The notice period for <strong>{{ $contract->title }}</strong> ends in {{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}.
            @endif
        @else
            @if($daysUntilExpiration === 0)
                Your contract <strong>{{ $contract->title }}</strong> expires today.
            @else
                Your contract <strong>{{ $contract->title }}</strong> expires in {{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}.
            @endif
        @endif
    </p>

    @php
        $details = [
            'Contract' => $contract->title,
            'Type' => $contract->contract_type,
        ];

        if ($contract->counterparty) {
            $details['Counterparty'] = $contract->counterparty;
        }

        $details['Start Date'] = $contract->start_date->format('F j, Y');
        $details['End Date'] = $contract->end_date->format('F j, Y');

        if ($contract->contract_value) {
            $currencyService = app(\App\Services\CurrencyService::class);
            $contractCurrency = $contract->currency ?? config('currency.default', 'MKD');
            $valueInDefault = $currencyService->convertToDefault($contract->contract_value, $contractCurrency);
            $details['Value'] = $currencyService->format($valueInDefault);
        }

        if ($isNoticeAlert && $contract->notice_period_days) {
            $details['Notice Period'] = $contract->notice_period_days . ' days';
            $details['Notice Deadline'] = $contract->notice_deadline->format('F j, Y');
        }

        if (!$isNoticeAlert) {
            $details['Auto Renewal'] = $contract->auto_renewal ? 'Enabled' : 'Manual';
        }
    @endphp

    <x-emails.components.detail-list :items="$details" />

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #706F6C; margin: 0 0 4px;" class="subtext">
        @if($isNoticeAlert)
            @if($daysUntilExpiration === 0)
                Act today if you wish to terminate this contract.
            @else
                Provide notice before the deadline if you wish to terminate.
            @endif
        @else
            @if($contract->auto_renewal)
                This contract has auto-renewal enabled.
            @else
                This contract requires manual renewal.
            @endif
        @endif
    </p>

    <x-emails.components.button :url="url('/contracts/' . $contract->id)">
        View Contract
    </x-emails.components.button>
@endsection
