<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }
        .company-info {
            text-align: right;
            float: right;
            width: 40%;
        }
        .company-info h1 {
            color: #667eea;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        .invoice-title {
            float: left;
            width: 50%;
            margin-top: 20px;
        }
        .invoice-title h2 {
            color: #333;
            font-size: 28px;
            margin: 0;
            font-weight: 300;
        }
        .invoice-number {
            color: #667eea;
            font-size: 16px;
            font-weight: bold;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .client-info {
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .client-info h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 14px;
            font-weight: bold;
        }
        .client-info p {
            margin: 3px 0;
        }
        .invoice-details {
            width: 100%;
            margin: 30px 0;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .invoice-details td {
            padding: 8px 15px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .invoice-details .label {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 150px;
            border-right: 0;
        }
        .invoice-details .value {
            border-left: 0;
        }
        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 30px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .items-table th {
            background: linear-gradient(to bottom, #667eea, #5a6fd8);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .items-table tbody tr:last-child td {
            border-bottom: none;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            float: right;
            width: 300px;
            margin: 20px 0;
        }
        .totals table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .totals td {
            padding: 8px 15px;
            border: 1px solid #ddd;
        }
        .totals .label {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 60%;
            border-right: 0;
            text-align: right;
        }
        .totals .value {
            text-align: right;
            border-left: 0;
            font-weight: bold;
        }
        .totals .total-row .label,
        .totals .total-row .value {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            font-size: 14px;
            font-weight: bold;
        }
        .notes, .terms {
            margin: 30px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 0 5px 5px 0;
        }
        .notes h4, .terms h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .notes p, .terms p {
            margin: 0;
            font-size: 11px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-draft {
            background-color: #e2e8f0;
            color: #4a5568;
        }
        .status-sent {
            background-color: #bee3f8;
            color: #2b6cb0;
        }
        .status-paid {
            background-color: #c6f6d5;
            color: #22543d;
        }
        .status-overdue {
            background-color: #fed7d7;
            color: #c53030;
        }
        .status-cancelled {
            background-color: #e2e8f0;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="company-info">
            <h1>{{ config('app.name', 'Your Company') }}</h1>
            <p>{{ config('company.address', '123 Business Street') }}</p>
            <p>{{ config('company.city', 'City') }}, {{ config('company.state', 'State') }} {{ config('company.postal_code', '12345') }}</p>
            <p>Phone: {{ config('company.phone', '(555) 123-4567') }}</p>
            <p>Email: {{ config('company.email', 'hello@company.com') }}</p>
        </div>
        <div class="invoice-title">
            <h2>INVOICE</h2>
            <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
            <div class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</div>
        </div>
    </div>

    <div class="client-info">
        <h3>Bill To:</h3>
        <p><strong>{{ $invoice->client->name }}</strong></p>
        <p>{{ $invoice->client->email }}</p>
        @if($invoice->client->phone)
            <p>{{ $invoice->client->phone }}</p>
        @endif
        @if($invoice->client->address)
            <p>{{ $invoice->client->address }}</p>
        @endif
        @if($invoice->client->city || $invoice->client->state || $invoice->client->postal_code)
            <p>
                {{ $invoice->client->city }}{{ $invoice->client->city && ($invoice->client->state || $invoice->client->postal_code) ? ', ' : '' }}
                {{ $invoice->client->state }} {{ $invoice->client->postal_code }}
            </p>
        @endif
        @if($invoice->client->country)
            <p>{{ $invoice->client->country }}</p>
        @endif
    </div>

    <div class="invoice-details">
        <table>
            <tr>
                <td class="label">Issue Date:</td>
                <td class="value">{{ $invoice->issue_date->format('F j, Y') }}</td>
                <td class="label">Due Date:</td>
                <td class="value">{{ $invoice->due_date->format('F j, Y') }}</td>
            </tr>
            @if($invoice->currency !== 'USD')
            <tr>
                <td class="label">Currency:</td>
                <td class="value">{{ $invoice->currency }}</td>
                <td class="label">Exchange Rate:</td>
                <td class="value">{{ number_format($invoice->exchange_rate, 4) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 15%;" class="text-center">Quantity</th>
                <th style="width: 17.5%;" class="text-right">Unit Price</th>
                <th style="width: 17.5%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals clearfix">
        <table>
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">${{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->tax_rate > 0)
            <tr>
                <td class="label">Tax ({{ number_format($invoice->tax_rate, 1) }}%):</td>
                <td class="value">${{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            @if($invoice->discount > 0)
            <tr>
                <td class="label">Discount:</td>
                <td class="value">-${{ number_format($invoice->discount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label">TOTAL:</td>
                <td class="value">${{ number_format($invoice->total, 2) }}</td>
            </tr>
            @if($invoice->amount_paid > 0)
            <tr>
                <td class="label">Paid:</td>
                <td class="value">-${{ number_format($invoice->amount_paid, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Balance Due:</td>
                <td class="value">${{ number_format($invoice->remaining_balance, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if($invoice->notes)
    <div class="notes clearfix">
        <h4>Notes</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    @if($invoice->terms)
    <div class="terms">
        <h4>Payment Terms</h4>
        <p>{{ $invoice->terms }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
