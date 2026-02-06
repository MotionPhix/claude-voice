<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1f2937;
            line-height: 1.6;
            padding: 40px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4F46E5;
        }

        .company-info h1 {
            color: #4F46E5;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .company-info p {
            color: #6b7280;
            font-size: 14px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            font-size: 36px;
            color: #4F46E5;
            margin-bottom: 4px;
        }

        .invoice-title p {
            color: #6b7280;
            font-size: 14px;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .detail-section h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .detail-section p {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .items-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }

        .items-table thead {
            background: #f9fafb;
        }

        .items-table th {
            text-align: left;
            padding: 12px;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .totals {
            margin-left: auto;
            width: 300px;
            margin-top: 20px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .totals-row.subtotal {
            color: #6b7280;
        }

        .totals-row.total {
            border-top: 2px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 12px;
            font-size: 18px;
            font-weight: bold;
            color: #4F46E5;
        }

        .notes-section {
            margin-top: 40px;
        }

        .notes-section h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .notes-section p {
            font-size: 14px;
            color: #4b5563;
            white-space: pre-wrap;
        }

        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ $organization->name }}</h1>
                @if($organization->email)
                    <p>{{ $organization->email }}</p>
                @endif
                @if($organization->phone)
                    <p>{{ $organization->phone }}</p>
                @endif
                @if($organization->address)
                    <p>{{ $organization->address }}</p>
                    <p>
                        @if($organization->city){{ $organization->city }}, @endif
                        @if($organization->state){{ $organization->state }} @endif
                        @if($organization->postal_code){{ $organization->postal_code }}@endif
                    </p>
                    @if($organization->country)
                        <p>{{ $organization->country }}</p>
                    @endif
                @endif
            </div>
            <div class="invoice-title">
                <h2>INVOICE</h2>
                <p>{{ $invoice->invoice_number }}</p>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="detail-section">
                <h3>Bill To</h3>
                <p><strong>{{ $invoice->client->name }}</strong></p>
                <p>{{ $invoice->client->email }}</p>
                @if($invoice->client->phone)
                    <p>{{ $invoice->client->phone }}</p>
                @endif
                @if($invoice->client->address)
                    <p>{{ $invoice->client->address }}</p>
                    <p>
                        @if($invoice->client->city){{ $invoice->client->city }}, @endif
                        @if($invoice->client->state){{ $invoice->client->state }} @endif
                        @if($invoice->client->postal_code){{ $invoice->client->postal_code }}@endif
                    </p>
                @endif
            </div>
            <div class="detail-section" style="text-align: right;">
                <h3>Invoice Details</h3>
                <p><strong>Issue Date:</strong> {{ $invoice->issue_date->format('M d, Y') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                <p><strong>Status:</strong> <span style="text-transform: capitalize;">{{ $invoice->status }}</span></p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-row subtotal">
                <span>Subtotal:</span>
                <span>{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount > 0)
                <div class="totals-row subtotal">
                    <span>Discount:</span>
                    <span>-{{ $currencySymbol }}{{ number_format($invoice->discount, 2) }}</span>
                </div>
            @endif
            @if($invoice->tax_rate > 0)
                <div class="totals-row subtotal">
                    <span>Tax ({{ $invoice->tax_rate }}%):</span>
                    <span>{{ $currencySymbol }}{{ number_format($invoice->tax, 2) }}</span>
                </div>
            @endif
            <div class="totals-row total">
                <span>Total:</span>
                <span>{{ $currencySymbol }}{{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>

        <!-- Notes & Terms -->
        @if($invoice->notes || $invoice->terms)
            <div class="notes-section">
                @if($invoice->notes)
                    <div style="margin-bottom: 20px;">
                        <h3>Notes</h3>
                        <p>{{ $invoice->notes }}</p>
                    </div>
                @endif
                @if($invoice->terms)
                    <div>
                        <h3>Terms & Conditions</h3>
                        <p>{{ $invoice->terms }}</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
