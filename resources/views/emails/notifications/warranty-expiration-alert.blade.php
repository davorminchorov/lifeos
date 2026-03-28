@extends('emails.layouts.base')

@section('preheader')
    @if($daysUntilExpiration === 0)
        Your {{ $warranty->product_name }} warranty expires today
    @else
        Your {{ $warranty->product_name }} warranty expires in {{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}
    @endif
@endsection

@section('content')
    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 600; color: #1B1B18; margin: 0 0 20px;" class="heading">Hello {{ $user->name }},</p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
        @if($daysUntilExpiration === 0)
            Your warranty is expiring <strong>today</strong>. This is your last chance to use your warranty coverage.
        @else
            Your warranty is set to expire in <strong>{{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}</strong>. Here are the details:
        @endif
    </p>

    <!-- Highlight card -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
        <tr>
            <td style="background-color: #FEF2F2; border-left: 4px solid #F53003; border-radius: 0 8px 8px 0; padding: 16px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="highlight-bg body-text">
                <strong>{{ $warranty->product_name }}</strong> warranty
                @if($daysUntilExpiration === 0)
                    expires today
                @else
                    expires {{ $warranty->warranty_expiration_date->format('F j, Y') }}
                @endif
            </td>
        </tr>
    </table>

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
        <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
            <strong>Act Now:</strong> Your warranty coverage ends today. If you are experiencing any issues with this product, contact the manufacturer or retailer immediately to file a warranty claim.
        </p>
    @else
        <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 8px;" class="body-text">
            Consider taking action before your warranty expires:
        </p>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Test your product to ensure it's working properly</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Review the warranty terms and coverage</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Check if warranty extension options are available</td></tr>
            <tr><td style="padding: 4px 0 4px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18;" class="body-text">&bull; Contact support if you have any issues</td></tr>
        </table>
    @endif

    <x-emails.components.button :url="url('/warranties/' . $warranty->id)">
        View Warranty Details
    </x-emails.components.button>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
        Keep your warranty information organized and accessible from your LifeOS dashboard.
    </p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0;" class="body-text">
        <strong>Best regards,</strong><br>
        The LifeOS Team
    </p>
@endsection
