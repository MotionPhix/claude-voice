<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        .header {
            margin-bottom: 30px;
        }
        .client-info {
            margin-bottom: 30px;
        }
        .invoice-info {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
        .totals {
            float: right;
            width: 300px;
        }
        .totals table {
            margin-bottom: 0;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice</h1>
        <p>{{ $invoice->invoice_number }}</p>
    </div>

    <div class="client-info">
        <h3>Bill To:</h3>
        <p>{{ $invoice->client->name }}<br>
        {{ $invoice->client->address }}</p>
    </div>

    <div class="invoice-info">
        <table>
            <tr>
                <td><strong>Issue Date:</strong></td>
                <td>{{ $invoice->issue_date->format('Y-m-d') }}</td>
                <td><strong>Due Date:</strong></td>
                <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td>{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Tax ({{ number_format($invoice->tax_rate, 2) }}%):</strong></td>
                <td>{{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td><strong>Discount:</strong></td>
                <td>{{ number_format($invoice->discount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Total:</strong></td>
                <td><strong>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
    <div style="clear: both; padding-top: 30px;">
        <h4>Notes:</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    @if($invoice->terms)
    <div class="footer">
        <h4>Terms & Conditions:</h4>
        <p>{{ $invoice->terms }}</p>
    </div>
    @endif
</body>
</html>
