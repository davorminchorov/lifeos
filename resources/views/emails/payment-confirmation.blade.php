<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }
        .header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #047857;
        }
        .message {
            margin-bottom: 30px;
            font-size: 15px;
            line-height: 1.7;
        }
        .payment-details {
            background-color: #f0fdf4;
            border-left: 4px solid #059669;
            padding: 20px;
            margin: 30px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #d1fae5;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #047857;
        }
        .detail-value {
            color: #1e293b;
            font-weight: 500;
        }
        .amount-highlight {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }
        .amount-label {
            font-size: 14px;
            color: #047857;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .amount-value {
            font-size: 36px;
            font-weight: 700;
            color: #065f46;
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background-color: #059669;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .checkmark {
            color: white;
            font-size: 36px;
            font-weight: bold;
        }
        .invoice-summary {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .invoice-summary h3 {
            margin: 0 0 15px 0;
            color: #1e293b;
            font-size: 16px;
        }
        .balance-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
        }
        .balance-row.total {
            border-top: 2px solid #cbd5e1;
            margin-top: 10px;
            padding-top: 15px;
            font-weight: 700;
            font-size: 16px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
        }
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .header {
                padding: 30px 20px;
            }
            .amount-value {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="icon">✓</div>
            <h1>Payment Received</h1>
            <p>Thank you for your payment!</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="success-icon">
                    <span class="checkmark">✓</span>
                </div>
            </div>

            <div class="greeting">
                Hello {{ $invoice->customer->name }},
            </div>

            @if($customMessage)
                <div class="message">
                    {!! nl2br(e($customMessage)) !!}
                </div>
                <div class="divider"></div>
            @else
                <div class="message">
                    We have successfully received your payment for Invoice <strong>{{ $invoice->number }}</strong>.
                    Thank you for your prompt payment!
                </div>
            @endif

            <!-- Payment Amount Highlight -->
            <div class="amount-highlight">
                <div class="amount-label">Payment Amount</div>
                <div class="amount-value">{{ $invoice->currency }} {{ number_format($payment->amount / 100, 2) }}</div>
            </div>

            <!-- Payment Details -->
            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ $payment->payment_date->format('F d, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value" style="text-transform: capitalize;">
                        {{ str_replace('_', ' ', $payment->payment_method) }}
                    </span>
                </div>
                @if($payment->reference)
                    <div class="detail-row">
                        <span class="detail-label">Reference:</span>
                        <span class="detail-value">{{ $payment->reference }}</span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Invoice Number:</span>
                    <span class="detail-value">{{ $invoice->number }}</span>
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="invoice-summary">
                <h3>Invoice Summary</h3>
                <div class="balance-row">
                    <span>Invoice Total:</span>
                    <span>{{ $invoice->currency }} {{ number_format($invoice->total / 100, 2) }}</span>
                </div>
                <div class="balance-row">
                    <span>Total Paid:</span>
                    <span style="color: #059669;">{{ $invoice->currency }} {{ number_format($invoice->amount_paid / 100, 2) }}</span>
                </div>
                <div class="balance-row total">
                    <span>Remaining Balance:</span>
                    <span style="color: {{ $invoice->amount_due > 0 ? '#dc2626' : '#059669' }};">
                        {{ $invoice->currency }} {{ number_format($invoice->amount_due / 100, 2) }}
                    </span>
                </div>
            </div>

            @if($invoice->amount_due <= 0)
                <div style="text-align: center; padding: 20px; background-color: #d1fae5; border-radius: 8px; margin: 20px 0;">
                    <p style="margin: 0; color: #065f46; font-weight: 600; font-size: 16px;">
                        🎉 This invoice has been paid in full!
                    </p>
                </div>
            @else
                <div style="text-align: center; padding: 15px; background-color: #fef3c7; border-radius: 8px; margin: 20px 0;">
                    <p style="margin: 0; color: #92400e; font-size: 14px;">
                        A balance of <strong>{{ $invoice->currency }} {{ number_format($invoice->amount_due / 100, 2) }}</strong> remains on this invoice.
                    </p>
                </div>
            @endif

            @if($payment->notes)
                <div class="divider"></div>
                <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0;">
                    <strong style="color: #92400e;">Payment Note:</strong>
                    <div style="color: #78350f; margin-top: 5px;">{{ $payment->notes }}</div>
                </div>
            @endif

            <div style="margin-top: 30px; padding: 15px; background-color: #f8fafc; border-radius: 6px; font-size: 14px; color: #64748b;">
                <p style="margin: 0;">
                    For your records, please keep this payment confirmation email. If you have any questions
                    about this payment or your invoice, please don't hesitate to contact us.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>LifeOS</strong></p>
            <p>Personal Life Management Platform</p>
            <p style="margin-top: 15px;">
                Thank you for your business!
            </p>
            <p style="margin-top: 10px; color: #94a3b8; font-size: 12px;">
                This is an automated message. Please do not reply directly to this email.
            </p>
        </div>
    </div>
</body>
</html>
