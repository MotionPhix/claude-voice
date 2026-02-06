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
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #0f172a;
            line-height: 1.6;
            padding: 40px;
            background: #f8fafc;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header {
            background: linear-gradient(135deg, #0EA5E9 0%, #06b6d4 100%);
            color: white;
            padding: 40px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-info h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .company-info p {
            opacity: 0.9;
            font-size: 14px;
        }

        .invoice-badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 16px 24px;
            border-radius: 12px;
            text-align: right;
        }

        .invoice-badge h2 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .invoice-badge p {
            font-size: 24px;
            font-weight: 700;
        }

        .content {
            padding: 40px;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .detail-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #0EA5E9;
        }

        .detail-card h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 12px;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .detail-card p {
            font-size: 14px;
            margin-bottom: 4px;
            color: #334155;
        }

        .detail-card p strong {
            color: #0f172a;
        }

        .items-section {
            margin-bottom: 30px;
        }

        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .items-table thead {
            background: #0EA5E9;
            color: white;
        }

        .items-table th {
            text-align: left;
            padding: 14px 16px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .items-table th:first-child {
            border-top-left-radius: 8px;
        }

        .items-table th:last-child {
            border-top-right-radius: 8px;
            text-align: right;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 14px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #334155;
        }

        .items-table td.text-right {
            text-align: right;
            font-weight: 500;
        }

        .items-table tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .items-table tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }

        .totals-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .totals {
            width: 350px;
            background: #f8fafc;
            padding: 24px;
            border-radius: 12px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: #64748b;
        }

        .totals-row span:last-child {
            color: #334155;
            font-weight: 500;
        }

        .totals-row.total {
            margin-top: 12px;
            padding-top: 16px;
            border-top: 2px solid #cbd5e1;
            font-size: 20px;
            font-weight: 700;
            color: #0EA5E9;
        }

        .totals-row.total span {
            color: #0EA5E9;
        }

        .notes-section {
            margin-top: 40px;
            padding: 24px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .notes-section h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 12px;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .notes-section p {
            font-size: 14px;
            color: #475569;
            white-space: pre-wrap;
            line-height: 1.8;
        }

        .footer {
            margin-top: 40px;
            padding: 24px 40px;
            background: #f8fafc;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }
            .invoice-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="company-info">
                    <h1>{{ $organization->name }}</h1>
                    @if($organization->email || $organization->phone)
                        <p>
                            @if($organization->email){{ $organization->email }}@endif
                            @if($organization->email && $organization->phone) • @endif
                            @if($organization->phone){{ $organization->phone }}@endif
                        </p>
                    @endif
                </div>
                <div class="invoice-badge">
                    <h2>Invoice</h2>
                    <p>{{ $invoice->invoice_number }}</p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Invoice Details -->
            <div class="invoice-details">
                <div class="detail-card">
                    <h3>Bill To</h3>
                    <p><strong>{{ $invoice->client->name }}</strong></p>
                    <p>{{ $invoice->client->email }}</p>
                    @if($invoice->client->phone)
                        <p>{{ $invoice->client->phone }}</p>
                    @endif
                    @if($invoice->client->address)
                        <p style="margin-top: 8px;">{{ $invoice->client->address }}</p>
                        <p>
                            @if($invoice->client->city){{ $invoice->client->city }}, @endif
                            @if($invoice->client->state){{ $invoice->client->state }} @endif
                            @if($invoice->client->postal_code){{ $invoice->client->postal_code }}@endif
                        </p>
                    @endif
                </div>
                <div class="detail-card">
                    <h3>Invoice Information</h3>
                    <p><strong>Issue Date:</strong> {{ $invoice->issue_date->format('M d, Y') }}</p>
                    <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                    <p><strong>Status:</strong> <span style="text-transform: capitalize; color: #0EA5E9;">{{ $invoice->status }}</span></p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="items-section">
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
            </div>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals">
                    <div class="totals-row">
                        <span>Subtotal</span>
                        <span>{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if($invoice->discount > 0)
                        <div class="totals-row">
                            <span>Discount</span>
                            <span>-{{ $currencySymbol }}{{ number_format($invoice->discount, 2) }}</span>
                        </div>
                    @endif
                    @if($invoice->tax_rate > 0)
                        <div class="totals-row">
                            <span>Tax ({{ $invoice->tax_rate }}%)</span>
                            <span>{{ $currencySymbol }}{{ number_format($invoice->tax, 2) }}</span>
                        </div>
                    @endif
                    <div class="totals-row total">
                        <span>Total Amount</span>
                        <span>{{ $currencySymbol }}{{ number_format($invoice->total, 2) }}</span>
                    </div>
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
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business! We appreciate your prompt payment.</p>
        </div>
    </div>
</body>
</html>
