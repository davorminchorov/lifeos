<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->number ?? 'Draft' }}</title>
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
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
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
            color: #1e40af;
        }
        .message {
            margin-bottom: 30px;
            font-size: 15px;
            line-height: 1.7;
        }
        .invoice-details {
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 20px;
            margin: 30px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
        }
        .detail-value {
            color: #1e293b;
            font-weight: 500;
        }
        .amount-highlight {
            background-color: #dbeafe;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }
        .amount-label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 5px;
        }
        .amount-value {
            font-size: 32px;
            font-weight: 700;
            color: #1e40af;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-draft {
            background-color: #f1f5f9;
            color: #475569;
        }
        .status-issued {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
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
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>LifeOS</h1>
            <p>Invoice {{ $invoice->number ?? 'Notification' }}</p>
        </div>

        <!-- Content -->
        <div class="content">
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
                    @if($invoice->status === \App\Enums\InvoiceStatus::ISSUED || $invoice->status === \App\Enums\InvoiceStatus::PAST_DUE)
                        Thank you for your business! Please find attached your invoice. Payment is due by <strong>{{ $invoice->due_at->format('F d, Y') }}</strong>.
                    @elseif($invoice->status === \App\Enums\InvoiceStatus::DRAFT)
                        We've prepared a draft invoice for your review. Please find it attached to this email.
                    @elseif($invoice->status === \App\Enums\InvoiceStatus::PAID)
                        Thank you for your payment! This invoice has been marked as paid.
                    @endif
                </div>
            @endif

            <!-- Invoice Details -->
            <div class="invoice-details">
                <div class="detail-row">
                    <span class="detail-label">Invoice Number:</span>
                    <span class="detail-value">{{ $invoice->number ?? 'Draft' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $invoice->status->value }}">
                            {{ $invoice->status->label() }}
                        </span>
                    </span>
                </div>
                @if($invoice->issued_at)
                    <div class="detail-row">
                        <span class="detail-label">Invoice Date:</span>
                        <span class="detail-value">{{ $invoice->issued_at->format('F d, Y') }}</span>
                    </div>
                @endif
                @if($invoice->due_at)
                    <div class="detail-row">
                        <span class="detail-label">Due Date:</span>
                        <span class="detail-value">{{ $invoice->due_at->format('F d, Y') }}</span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Payment Terms:</span>
                    <span class="detail-value">Net {{ $invoice->payment_terms }} days</span>
                </div>
            </div>

            <!-- Amount Highlight -->
            @if($invoice->amount_due > 0)
                <div class="amount-highlight">
                    <div class="amount-label">Amount Due</div>
                    <div class="amount-value">{{ $invoice->currency }} {{ number_format($invoice->amount_due / 100, 2) }}</div>
                </div>
            @else
                <div class="amount-highlight">
                    <div class="amount-label">Total Amount</div>
                    <div class="amount-value">{{ $invoice->currency }} {{ number_format($invoice->total / 100, 2) }}</div>
                </div>
            @endif

            <div style="text-align: center;">
                <p style="color: #64748b; font-size: 14px;">
                    The complete invoice is attached as a PDF to this email.
                </p>
            </div>

            @if($invoice->notes)
                <div class="divider"></div>
                <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0;">
                    <strong style="color: #92400e;">Note:</strong>
                    <div style="color: #78350f; margin-top: 5px;">{{ $invoice->notes }}</div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>LifeOS</strong></p>
            <p>Personal Life Management Platform</p>
            <p style="margin-top: 15px;">
                If you have any questions about this invoice, please contact us.
            </p>
            <p style="margin-top: 10px; color: #94a3b8; font-size: 12px;">
                This is an automated message. Please do not reply directly to this email.
            </p>
        </div>
    </div>
</body>
</html>
