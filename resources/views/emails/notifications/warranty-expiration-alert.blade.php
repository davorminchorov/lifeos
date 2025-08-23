@extends('emails.layouts.base')

@section('content')
    <div class="greeting">Hello {{ $user->name }}!</div>

    <div class="content-text">
        @if($daysUntilExpiration === 0)
            Your warranty is expiring <strong>today</strong>! This is your last chance to use your warranty coverage.
        @else
            Your warranty is set to expire in <strong>{{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}</strong>. Here are the details:
        @endif
    </div>

    <div class="highlight">
        <strong>{{ $warranty->product_name }}</strong> warranty
        @if($daysUntilExpiration === 0)
            expires today
        @else
            expires {{ $warranty->warranty_expiration_date->format('F j, Y') }}
        @endif
    </div>

    @php
        $details = [
            'Product' => $warranty->product_name,
            'Purchase Date' => $warranty->purchase_date->format('F j, Y'),
            'Warranty Expires' => $warranty->warranty_expiration_date->format('F j, Y'),
        ];

        if ($warranty->brand) {
            $details['Brand'] = $warranty->brand;
        }

        if ($warranty->model) {
            $details['Model'] = $warranty->model;
        }

        if ($warranty->warranty_type) {
            $details['Warranty Type'] = $warranty->warranty_type;
        }

        if ($warranty->retailer) {
            $details['Purchased From'] = $warranty->retailer;
        }
    @endphp

    <x-emails.components.detail-list :items="$details" />

    @if($daysUntilExpiration === 0)
        <div class="content-text">
            <strong>Act Now:</strong> Your warranty coverage ends today. If you're experiencing any issues with this product, contact the manufacturer or retailer immediately to file a warranty claim.
        </div>
    @else
        <div class="content-text">
            Consider taking action before your warranty expires:
        </div>
        <div class="content-text">
            • Test your product to ensure it's working properly<br>
            • Review the warranty terms and coverage<br>
            • Check if warranty extension options are available<br>
            • Contact support if you have any issues
        </div>
    @endif

    <x-emails.components.button :url="url('/warranties/' . $warranty->id)">
        View Warranty Details
    </x-emails.components.button>

    <div class="content-text">
        Keep your warranty information organized and accessible from your LifeOS dashboard. You can also set up maintenance reminders to help protect your investment.
    </div>

    <div class="content-text">
        <strong>Best regards,</strong><br>
        The LifeOS Team
    </div>
@endsection
