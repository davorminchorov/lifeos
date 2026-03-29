@extends('emails.layouts.base')

@section('preheader')
    @if($daysUntilExpiration === 0)
        Your {{ $warranty->product_name }} warranty expires today
    @else
        Your {{ $warranty->product_name }} warranty expires in {{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}
    @endif
@endsection

@section('content')
    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; color: #1B1B18; margin: 0 0 16px;" class="heading">Hello {{ $user->name }},</p>

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18; margin: 0 0 20px;" class="body-text">
        @if($daysUntilExpiration === 0)
            Your warranty for <strong>{{ $warranty->product_name }}</strong> expires today.
        @else
            Your warranty for <strong>{{ $warranty->product_name }}</strong> expires in {{ $daysUntilExpiration }} {{ Str::plural('day', $daysUntilExpiration) }}.
        @endif
    </p>

    @php
        $details = [
            'Product' => $warranty->product_name,
        ];

        if ($warranty->brand) {
            $details['Manufacturer'] = $warranty->brand;
        }

        $details['Purchase Date'] = $warranty->purchase_date->format('F j, Y');
        $details['Expires'] = $warranty->warranty_expiration_date->format('F j, Y');

        if ($warranty->warranty_type) {
            $details['Coverage'] = $warranty->warranty_type;
        }

        if ($warranty->model) {
            $details['Model'] = $warranty->model;
        }

        if ($warranty->retailer) {
            $details['Retailer'] = $warranty->retailer;
        }
    @endphp

    <x-emails.components.detail-list :items="$details" />

    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #706F6C; margin: 0 0 4px;" class="subtext">
        @if($daysUntilExpiration === 0)
            File any warranty claims before end of day.
        @else
            Check for issues or extension options before coverage ends.
        @endif
    </p>

    <x-emails.components.button :url="url('/warranties/' . $warranty->id)">
        View Warranty
    </x-emails.components.button>
@endsection
