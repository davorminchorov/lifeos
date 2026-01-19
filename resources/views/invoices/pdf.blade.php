<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number ?? 'Draft' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
        }

        .container {
            padding: 40px;
        }

        .header {
            margin-bottom: 40px;
        }

        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .header-right {
            text-align: right;
        }

        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .invoice-title {
            font-size: 28pt;
            font-weight: bold;
            color: #1e40af;
        }

        .invoice-number {
            font-size: 14pt;
            color: #666;
            margin-top: 5px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            margin-top: 20px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .bill-to,
        .invoice-details {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .info-label {
            color: #666;
            font-size: 9pt;
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: bold;
            margin-bottom: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft { background-color: #e5e7eb; color: #374151; }
        .status-issued { background-color: #dbeafe; color: #1e40af; }
        .status-partially_paid { background-color: #fef3c7; color: #92400e; }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-past_due { background-color: #fee2e2; color: #991b1b; }
        .status-void { background-color: #f3f4f6; color: #6b7280; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table thead {
            background-color: #1e40af;
            color: white;
        }

        table thead th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
        }

        table tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        table tbody tr:last-child td {
            border-bottom: 2px solid #1e40af;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            margin-top: 20px;
            float: right;
            width: 300px;
        }

        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .total-label {
            display: table-cell;
            text-align: right;
            padding-right: 20px;
            color: #666;
        }

        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }

        .grand-total {
            border-top: 2px solid #1e40af;
            padding-top: 10px;
            margin-top: 10px;
        }

        .grand-total .total-label,
        .grand-total .total-value {
            font-size: 14pt;
            color: #1e40af;
        }

        .payment-section {
            clear: both;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .payment-table {
            width: 100%;
            margin-top: 10px;
        }

        .payment-table thead {
            background-color: #f3f4f6;
            color: #374151;
        }

        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 4px solid #2563eb;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #1e40af;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 9pt;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-row">
                <div class="header-left">
                    <div class="company-name">LifeOS</div>
                    <div>Personal Life Management Platform</div>
                </div>
                <div class="header-right">
                    <div class="invoice-title">INVOICE</div>
                    @if($invoice->number)
                        <div class="invoice-number"># {{ $invoice->number }}</div>
                    @else
                        <div class="invoice-number">DRAFT</div>
                    @endif
                    <div style="margin-top: 10px;">
                        <span class="status-badge status-{{ $invoice->status->value }}">
                            {{ $invoice->status->label() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bill To & Invoice Details -->
        <div class="info-row">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="info-value">{{ $invoice->customer->name }}</div>
                @if($invoice->customer->company_name)
                    <div>{{ $invoice->customer->company_name }}</div>
                @endif
                @if($invoice->customer->email)
                    <div>{{ $invoice->customer->email }}</div>
                @endif
                @if($invoice->customer->phone)
                    <div>{{ $invoice->customer->phone }}</div>
                @endif
                @if($invoice->customer->billing_address)
                    <div style="margin-top: 10px;">
                        {{ $invoice->customer->billing_address }}<br>
                        @if($invoice->customer->billing_city)
                            {{ $invoice->customer->billing_city }},
                        @endif
                        @if($invoice->customer->billing_state)
                            {{ $invoice->customer->billing_state }}
                        @endif
                        @if($invoice->customer->billing_postal_code)
                            {{ $invoice->customer->billing_postal_code }}
                        @endif
                        @if($invoice->customer->billing_country)
                            <br>{{ $invoice->customer->billing_country }}
                        @endif
                    </div>
                @endif
            </div>
            <div class="invoice-details">
                @if($invoice->issued_at)
                    <div class="info-label">Invoice Date</div>
                    <div class="info-value">{{ $invoice->issued_at->format('M d, Y') }}</div>
                @endif

                @if($invoice->due_at)
                    <div class="info-label">Due Date</div>
                    <div class="info-value">{{ $invoice->due_at->format('M d, Y') }}</div>
                @endif

                <div class="info-label">Currency</div>
                <div class="info-value">{{ $invoice->currency }}</div>

                @if($invoice->customer->tax_id)
                    <div class="info-label">Tax ID</div>
                    <div class="info-value">{{ $invoice->customer->tax_id }}</div>
                @endif

                <div class="info-label">Payment Terms</div>
                <div class="info-value">Net {{ $invoice->payment_terms }} days</div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="section-title">Items</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    @if($invoice->items->where('tax_amount', '>', 0)->count() > 0)
                        <th class="text-right" style="width: 10%;">Tax</th>
                    @endif
                    @if($invoice->items->where('discount_amount', '>', 0)->count() > 0)
                        <th class="text-right" style="width: 10%;">Discount</th>
                    @endif
                    <th class="text-right" style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}</td>
                        <td class="text-right">{{ number_format($item->unit_amount / 100, 2) }}</td>
                        @if($invoice->items->where('tax_amount', '>', 0)->count() > 0)
                            <td class="text-right">
                                @if($item->tax_amount > 0)
                                    {{ number_format($item->tax_amount / 100, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                        @if($invoice->items->where('discount_amount', '>', 0)->count() > 0)
                            <td class="text-right">
                                @if($item->discount_amount > 0)
                                    -{{ number_format($item->discount_amount / 100, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                        <td class="text-right">{{ number_format($item->total / 100, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="clearfix">
            <div class="totals-section">
                <div class="total-row">
                    <div class="total-label">Subtotal:</div>
                    <div class="total-value">{{ number_format($invoice->subtotal / 100, 2) }}</div>
                </div>

                @if($invoice->discount_total > 0)
                    <div class="total-row">
                        <div class="total-label">Discount:</div>
                        <div class="total-value" style="color: #059669;">-{{ number_format($invoice->discount_total / 100, 2) }}</div>
                    </div>
                @endif

                @if($invoice->tax_total > 0)
                    <div class="total-row">
                        <div class="total-label">Tax:</div>
                        <div class="total-value">{{ number_format($invoice->tax_total / 100, 2) }}</div>
                    </div>
                @endif

                <div class="total-row grand-total">
                    <div class="total-label">Total:</div>
                    <div class="total-value">{{ $invoice->currency }} {{ number_format($invoice->total / 100, 2) }}</div>
                </div>

                @if($invoice->amount_paid > 0)
                    <div class="total-row" style="margin-top: 15px;">
                        <div class="total-label">Amount Paid:</div>
                        <div class="total-value" style="color: #059669;">{{ number_format($invoice->amount_paid / 100, 2) }}</div>
                    </div>

                    <div class="total-row">
                        <div class="total-label">Amount Due:</div>
                        <div class="total-value" style="color: #dc2626;">{{ number_format($invoice->amount_due / 100, 2) }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payments -->
        @if($invoice->payments->count() > 0)
            <div class="payment-section">
                <div class="section-title">Payment History</div>
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td class="text-right">{{ number_format($payment->amount / 100, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
            <div class="notes-section">
                <div class="notes-title">Notes</div>
                <div>{{ $invoice->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div style="margin-top: 5px;">
                Generated on {{ now()->format('F d, Y') }}
            </div>
        </div>
    </div>
</body>
</html>
