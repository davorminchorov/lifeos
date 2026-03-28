<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>Payment Confirmation</title>
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
            .card-bg { background-color: #252521 !important; }
            .border-color { border-color: #3E3E3A !important; }
            .detail-label-dark { color: #A1A09A !important; }
            .detail-value-dark { color: #EDEDEC !important; }
            .footer-text-dark { color: #706F6C !important; }
            .footer-muted-dark { color: #62605B !important; }
            .amount-bg { background-color: #252521 !important; }
            .amount-text-dark { color: #EDEDEC !important; }
            .amount-label-dark { color: #A1A09A !important; }
            .success-bg { background-color: rgba(34, 197, 94, 0.1) !important; }
            .highlight-bg { background-color: #252521 !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F5F4F0;">
    <div class="preheader" style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">
        Payment received for Invoice {{ $invoice->number }}
        &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #F5F4F0;" class="email-bg">
        <tr>
            <td style="padding: 32px 16px;" class="padding-mobile">
                <div style="max-width: 560px; margin: 0 auto;">

                    <!--[if mso]>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="560" align="center"><tr><td>
                    <![endif]-->

                    <!-- Header -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container" style="max-width: 560px; margin: 0 auto;">
                        <tr>
                            <td style="padding: 28px 0; text-align: center;">
                                <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #1B1B18; letter-spacing: -0.5px;" class="heading">Life<span style="color: #F53003;">OS</span></span>
                            </td>
                        </tr>
                    </table>

                    <!-- Body card -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container email-body" style="max-width: 560px; margin: 0 auto; background-color: #FDFDFC; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(27,27,24,0.08);">
                        <tr>
                            <td style="padding: 40px 36px;" class="padding-mobile">

                                <!-- Success indicator -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 28px;">
                                    <tr>
                                        <td style="text-align: center;">
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                                <tr>
                                                    <td style="width: 48px; height: 48px; background-color: #ECFDF5; border-radius: 50%; text-align: center; vertical-align: middle; font-size: 22px; color: #059669;" class="success-bg">&#10003;</td>
                                                </tr>
                                            </table>
                                            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #059669; margin: 12px 0 0;">Payment Received</p>
                                        </td>
                                    </tr>
                                </table>

                                <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 600; color: #1B1B18; margin: 0 0 20px;" class="heading">Hello {{ $invoice->customer->name }},</p>

                                @if($customMessage)
                                    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
                                        {!! nl2br(e($customMessage)) !!}
                                    </p>
                                @else
                                    <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; margin: 0 0 24px;" class="body-text">
                                        We have successfully received your payment for Invoice <strong>{{ $invoice->number }}</strong>. Thank you for your prompt payment!
                                    </p>
                                @endif

                                <!-- Payment amount highlight -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 28px;">
                                    <tr>
                                        <td style="background-color: #F8F7F4; border-radius: 10px; padding: 24px; text-align: center;" class="amount-bg">
                                            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; font-weight: 500; color: #706F6C; margin: 0 0 6px; text-transform: uppercase; letter-spacing: 0.5px;" class="amount-label-dark">Payment Amount</p>
                                            <p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 32px; font-weight: 700; color: #1B1B18; margin: 0; letter-spacing: -1px;" class="amount-text-dark">{{ $invoice->currency }} {{ number_format($payment->amount / 100, 2) }}</p>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Payment details -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 28px; background-color: #F8F7F4; border-radius: 8px; border: 1px solid #E3E3E0;" class="card-bg border-color">
                                    <tr>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #706F6C; font-weight: 500; width: 40%; border-bottom: 1px solid #E3E3E0;" class="detail-label-dark border-color">Payment Date</td>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1B1B18; font-weight: 600; text-align: right; border-bottom: 1px solid #E3E3E0;" class="detail-value-dark border-color">{{ $payment->payment_date->format('F d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #706F6C; font-weight: 500; border-bottom: 1px solid #E3E3E0;" class="detail-label-dark border-color">Payment Method</td>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1B1B18; font-weight: 600; text-align: right; text-transform: capitalize; border-bottom: 1px solid #E3E3E0;" class="detail-value-dark border-color">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                    </tr>
                                    @if($payment->reference)
                                    <tr>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #706F6C; font-weight: 500; border-bottom: 1px solid #E3E3E0;" class="detail-label-dark border-color">Reference</td>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1B1B18; font-weight: 600; text-align: right; border-bottom: 1px solid #E3E3E0;" class="detail-value-dark border-color">{{ $payment->reference }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #706F6C; font-weight: 500;" class="detail-label-dark">Invoice Number</td>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1B1B18; font-weight: 600; text-align: right;" class="detail-value-dark">{{ $invoice->number }}</td>
                                    </tr>
                                </table>

                                <!-- Invoice summary -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 28px; background-color: #F8F7F4; border-radius: 8px; border: 1px solid #E3E3E0;" class="card-bg border-color">
                                    <tr>
                                        <td colspan="2" style="padding: 14px 20px 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; color: #1B1B18; border-bottom: 1px solid #E3E3E0;" class="heading border-color">Invoice Summary</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #706F6C; font-weight: 500; border-bottom: 1px solid #E3E3E0;" class="detail-label-dark border-color">Invoice Total</td>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1B1B18; font-weight: 600; text-align: right; border-bottom: 1px solid #E3E3E0;" class="detail-value-dark border-color">{{ $invoice->currency }} {{ number_format($invoice->total / 100, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #706F6C; font-weight: 500; border-bottom: 1px solid #E3E3E0;" class="detail-label-dark border-color">Total Paid</td>
                                        <td style="padding: 12px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #059669; font-weight: 600; text-align: right; border-bottom: 1px solid #E3E3E0;" class="border-color">{{ $invoice->currency }} {{ number_format($invoice->amount_paid / 100, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 14px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1B1B18; font-weight: 700;" class="heading">Remaining Balance</td>
                                        <td style="padding: 14px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 700; text-align: right; color: {{ $invoice->amount_due > 0 ? '#DC2626' : '#059669' }};">{{ $invoice->currency }} {{ number_format($invoice->amount_due / 100, 2) }}</td>
                                    </tr>
                                </table>

                                @if($invoice->amount_due <= 0)
                                    <!-- Paid in full -->
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
                                        <tr>
                                            <td style="background-color: #ECFDF5; border-radius: 8px; padding: 16px; text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; color: #065F46;" class="success-bg">
                                                This invoice has been paid in full.
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <!-- Balance remaining -->
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
                                        <tr>
                                            <td style="background-color: #FFFBEB; border-radius: 8px; padding: 16px; text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #92400E;">
                                                A balance of <strong>{{ $invoice->currency }} {{ number_format($invoice->amount_due / 100, 2) }}</strong> remains on this invoice.
                                            </td>
                                        </tr>
                                    </table>
                                @endif

                                @if($payment->notes)
                                    <!-- Payment note -->
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 24px;">
                                        <tr>
                                            <td style="background-color: #FFFBEB; border-left: 4px solid #F59E0B; border-radius: 0 8px 8px 0; padding: 14px 18px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 22px; color: #1B1B18;" class="highlight-bg body-text">
                                                <strong style="color: #92400E;">Payment Note:</strong>
                                                <span style="display: block; margin-top: 4px;">{{ $payment->notes }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                @endif

                                <!-- Records note -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0;">
                                    <tr>
                                        <td style="background-color: #F8F7F4; border-radius: 8px; padding: 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 22px; color: #706F6C;" class="card-bg subtext">
                                            For your records, please keep this payment confirmation email. If you have any questions about this payment or your invoice, please don't hesitate to contact us.
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </table>

                    <!-- Footer -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container" style="max-width: 560px; margin: 0 auto;">
                        <tr>
                            <td style="padding: 28px 36px 8px;" class="padding-mobile">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #A1A09A;" class="footer-text-dark">
                                            <p style="margin: 0 0 12px;">
                                                Thank you for your business!
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
