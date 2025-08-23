@extends('emails.layouts.base')

@section('content')
    <div class="greeting">Hello {{ $user->name }}!</div>

    <div class="content-text">
        @if($isNoticeAlert)
            @if($daysUntilExpiration === 0)
                Your contract notice period deadline is <strong>today</strong>! This is your last chance to provide termination notice if you don't wish to continue.
            @else
                Your contract notice period ends in <strong>{{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}</strong>. Here are the details:
            @endif
        @else
            @if($daysUntilExpiration === 0)
                Your contract is expiring <strong>today</strong>! Please review your renewal options or termination requirements.
            @else
                Your contract is set to expire in <strong>{{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}</strong>. Here are the details:
            @endif
        @endif
    </div>

    <div class="highlight">
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
    </div>

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
            $details['Contract Value'] = '$' . number_format($contract->contract_value, 2);
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
            <div class="content-text">
                <strong>Act Now:</strong> Today is the deadline to provide termination notice. If you want to end this contract, you must notify the counterparty immediately according to the contract terms.
            </div>
        @else
            <div class="content-text">
                <strong>Important:</strong> If you want to terminate this contract, you must provide notice before the deadline. Review the contract terms for specific notice requirements and procedures.
            </div>
        @endif
    @else
        @if($contract->auto_renewal)
            <div class="content-text">
                This contract has auto-renewal enabled and will continue automatically unless terminated. Review the terms and consider if you want to make any changes or provide termination notice.
            </div>
        @else
            <div class="content-text">
                <strong>Action Required:</strong> This contract requires manual renewal. If you wish to continue, contact the counterparty to discuss renewal terms and execute a new agreement.
            </div>
        @endif

        <div class="content-text">
            Consider the following actions:
        </div>
        <div class="content-text">
            • Review contract performance and satisfaction<br>
            • Negotiate improved terms if renewing<br>
            • Compare with alternative providers<br>
            • Plan for transition if not renewing
        </div>
    @endif

    <x-emails.components.button :url="url('/contracts/' . $contract->id)">
        View Contract Details
    </x-emails.components.button>

    <div class="content-text">
        Keep all your contract information organized and track important dates with LifeOS. Set up reminders for renewals and termination deadlines to stay on top of your agreements.
    </div>

    <div class="content-text">
        <strong>Best regards,</strong><br>
        The LifeOS Team
    </div>
@endsection
