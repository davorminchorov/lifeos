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
    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 600; color: #1B1B18; margin: 0 0 20px;" class="heading">Hello {{ $user->name }},</p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
        @if($isNoticeAlert)
            @if($daysUntilExpiration === 0)
                Your contract notice period deadline is <strong>today</strong>. This is your last chance to provide termination notice if you don't wish to continue.
            @else
                Your contract notice period ends in <strong>{{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}</strong>. Here are the details:
            @endif
        @else
            @if($daysUntilExpiration === 0)
                Your contract is expiring <strong>today</strong>. Please review your renewal options or termination requirements.
            @else
                Your contract is set to expire in <strong>{{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}</strong>. Here are the details:
            @endif
        @endif
    </p>

    <!-- Highlight card -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
        <tr>
            <td style="background-color: #FEF2F2; border-left: 4px solid #F53003; border-radius: 0 8px 8px 0; padding: 16px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="highlight-bg body-text">
                <strong>{{ $contract->title }}</strong>
                @if($isNoticeAlert)
                    notice period
                    @if($daysUntilExpiration === 0)
                        deadline is today
                    @else
                        ends {{ $contract->notice_deadline->format('F j, Y') }}
                    @endif
                @else
                    @if($daysUntilExpiration === 0)
                        expires today
                    @else
                        expires {{ $contract->end_date->format('F j, Y') }}
                    @endif
                @endif
            </td>
        </tr>
    </table>

    @php
        $details = [
            'Contract' => $contract->title,
            'Contract Type' => $contract->contract_type,
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
            $details['Contract Value'] = $currencyService->format($valueInDefault);
        }

        if ($isNoticeAlert && $contract->notice_period_days) {
            $details['Notice Period'] = $contract->notice_period_days . ' days';
            $details['Notice Deadline'] = $contract->notice_deadline->format('F j, Y');
        }

        if (!$isNoticeAlert) {
            $details['Auto Renewal'] = $contract->auto_renewal ? 'Enabled' : 'Manual renewal required';
        }
    @endphp

    <x-emails.components.detail-list :items="$details" />

    @if($isNoticeAlert)
        @if($daysUntilExpiration === 0)
            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
                <strong>Act Now:</strong> Today is the deadline to provide termination notice. If you want to end this contract, you must notify the counterparty immediately according to the contract terms.
            </p>
        @else
            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
                <strong>Important:</strong> If you want to terminate this contract, you must provide notice before the deadline. Review the contract terms for specific notice requirements and procedures.
            </p>
        @endif
    @else
        @if($contract->auto_renewal)
            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
                This contract has auto-renewal enabled and will continue automatically unless terminated. Review the terms and consider if you want to make any changes.
            </p>
        @else
            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
                <strong>Action Required:</strong> This contract requires manual renewal. If you wish to continue, contact the counterparty to discuss renewal terms.
            </p>
        @endif

        <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 16px 0 8px;" class="body-text">
            Consider the following actions:
        </p>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Review contract performance and satisfaction</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Negotiate improved terms if renewing</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Compare with alternative providers</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Plan for transition if not renewing</td></tr>
        </table>
    @endif

    <x-emails.components.button :url="url('/contracts/' . $contract->id)">
        View Contract Details
    </x-emails.components.button>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
        Keep all your contract information organized and track important dates with LifeOS.
    </p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0;" class="body-text">
        <strong>Best regards,</strong><br>
        The LifeOS Team
    </p>
@endsection
