<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>Invoice {{ $invoice->number ?? 'Draft' }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .preheader { display: none !important; visibility: hidden; mso-hide: all; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; }
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; max-width: 100% !important; }
            .padding-mobile { padding-left: 20px !important; padding-right: 20px !important; }
        }
        @media (prefers-color-scheme: dark) {
            body, .email-bg { background-color: #161615 !important; }
            .email-body { background-color: #1B1B18 !important; }
            .body-text, .heading { color: #EDEDEC !important; }
            .subtext { color: #A1A09A !important; }
            .card-bg { background-color: #232320 !important; }
            .border-color { border-color: #3E3E3A !important; }
            .detail-label-dark { color: #A1A09A !important; }
            .detail-value-dark { color: #EDEDEC !important; }
            .footer-text-dark { color: #706F6C !important; }
            .footer-muted-dark { color: #62605B !important; }
            .amount-bg { background-color: #232320 !important; }
            .amount-text-dark { color: #EDEDEC !important; }
            .amount-label-dark { color: #A1A09A !important; }
            .highlight-bg { background-color: #232320 !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F5F4F0;">
    <div class="preheader" style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">
        Invoice {{ $invoice->number ?? 'Draft' }} from LifeOS
        &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #F5F4F0;" class="email-bg">
        <tr>
            <td style="padding: 40px 16px;" class="padding-mobile">
                <div style="max-width: 540px; margin: 0 auto;">

                    <!--[if mso]>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="540" align="center"><tr><td>
                    <![endif]-->

                    <!-- Header -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container" style="max-width: 540px; margin: 0 auto;">
                        <tr>
                            <td style="padding: 0 0 32px; text-align: center;">
                                <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 700; color: #1B1B18; letter-spacing: -0.3px;" class="heading">LifeOS</span>
                            </td>
                        </tr>
                    </table>

                    <!-- Body card -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container email-body" style="max-width: 540px; margin: 0 auto; background-color: #FDFDFC; border-radius: 10px; border: 1px solid #E3E3E0;">
                        <tr>
                            <td style="padding: 36px 32px;" class="padding-mobile">

                                <!-- Status badge -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
                                    <tr>
                                        <td>
                                            <span style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; padding: 4px 10px; border-radius: 4px;
                                                @if($invoice->status === \App\Enums\InvoiceStatus::PAID) background-color: #ECFDF5; color: #065F46;
                                                @elseif($invoice->status === \App\Enums\InvoiceStatus::ISSUED || $invoice->status === \App\Enums\InvoiceStatus::PAST_DUE) background-color: #FEF2F2; color: #991B1B;
                                                @else background-color: #F8F7F4; color: #706F6C;
                                                @endif">{{ $invoice->status->label() }}</span>
                                        </td>
                                    </tr>
                                </table>

                                <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; color: #1B1B18; margin: 0 0 16px;" class="heading">Hello {{ $invoice->customer->name }},</p>

                                @if($customMessage)
                                    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18; margin: 0 0 20px;" class="body-text">
                                        {!! nl2br(e($customMessage)) !!}
                                    </p>
                                @else
                                    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 24px; color: #1B1B18; margin: 0 0 20px;" class="body-text">
                                        @if($invoice->status === \App\Enums\InvoiceStatus::ISSUED || $invoice->status === \App\Enums\InvoiceStatus::PAST_DUE)
                                            Please find your invoice attached. Payment is due by <strong>{{ $invoice->due_at->format('F d, Y') }}</strong>.
                                        @elseif($invoice->status === \App\Enums\InvoiceStatus::DRAFT)
                                            A draft invoice has been prepared for your review.
                                        @elseif($invoice->status === \App\Enums\InvoiceStatus::PAID)
                                            This invoice has been paid. Thank you.
                                        @endif
                                    </p>
                                @endif

                                <!-- Amount -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
                                    <tr>
                                        <td style="background-color: #F8F7F4; border-radius: 8px; padding: 20px; text-align: center;" class="amount-bg">
                                            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 500; color: #706F6C; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 0.5px;" class="amount-label-dark">
                                                @if($invoice->amount_due > 0) Amount Due @else Total @endif
                                            </p>
                                            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 28px; font-weight: 700; color: #1B1B18; margin: 0; letter-spacing: -0.5px;" class="amount-text-dark">
                                                @if($invoice->amount_due > 0)
                                                    {{ $invoice->currency }} {{ number_format($invoice->amount_due / 100, 2) }}
                                                @else
                                                    {{ $invoice->currency }} {{ number_format($invoice->total / 100, 2) }}
                                                @endif
                                            </p>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Invoice details -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px; border-radius: 8px; border: 1px solid #E3E3E0;" class="card-bg border-color">
                                    <tr>
                                        <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #706F6C; font-weight: 500; width: 40%; border-bottom: 1px solid #E3E3E0;" class="detail-label-dark border-color">Invoice Number</td>
                                        <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #1B1B18; font-weight: 600; text-align: right; border-bottom: 1px solid #E3E3E0;" class="detail-value-dark border-color">{{ $invoice->number ?? 'Draft' }}</td>
                                    </tr>
                                    @if($invoice->issued_at)
                                    <tr>
                                        <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #706F6C; font-weight: 500; border-bottom: 1px solid #E3E3E0;" class="detail-label-dark border-color">Invoice Date</td>
                                        <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #1B1B18; font-weight: 600; text-align: right; border-bottom: 1px solid #E3E3E0;" class="detail-value-dark border-color">{{ $invoice->issued_at->format('F d, Y') }}</td>
                                    </tr>
                                    @endif
                                    @if($invoice->due_at)
                                    <tr>
                                        <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #706F6C; font-weight: 500; border-bottom: 1px solid #E3E3E0;" class="detail-label-dark border-color">Due Date</td>
                                        <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #1B1B18; font-weight: 600; text-align: right; border-bottom: 1px solid #E3E3E0;" class="detail-value-dark border-color">{{ $invoice->due_at->format('F d, Y') }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #706F6C; font-weight: 500;" class="detail-label-dark">Payment Terms</td>
                                        <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #1B1B18; font-weight: 600; text-align: right;" class="detail-value-dark">Net {{ $invoice->payment_terms }} days</td>
                                    </tr>
                                </table>

                                <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #706F6C; margin: 0;" class="subtext">
                                    The complete invoice is attached as a PDF.
                                </p>

                                @if($invoice->notes)
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0 0;">
                                        <tr>
                                            <td style="background-color: #F8F7F4; border-radius: 8px; border: 1px solid #E3E3E0; padding: 12px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #1B1B18;" class="highlight-bg body-text border-color">
                                                <strong style="color: #706F6C;">Note:</strong> {{ $invoice->notes }}
                                            </td>
                                        </tr>
                                    </table>
                                @endif

                            </td>
                        </tr>
                    </table>

                    <!-- Footer -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container" style="max-width: 540px; margin: 0 auto;">
                        <tr>
                            <td style="padding: 24px 32px 8px;" class="padding-mobile">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; line-height: 18px; color: #A1A09A;" class="footer-text-dark">
                                            <p style="margin: 0 0 8px;">
                                                Questions about this invoice? Contact us.
                                            </p>
                                            <p style="margin: 0; color: #C4C4BE;" class="footer-muted-dark">
                                                &copy; {{ date('Y') }} LifeOS
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!--[if mso]>
                    </td></tr></table>
                    <![endif]-->

                </div>
            </td>
        </tr>
    </table>
</body>
</html>
