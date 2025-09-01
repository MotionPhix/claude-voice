<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 25px;
            color: #2d3748;
        }
        .invoice-details {
            background-color: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 6px 6px 0;
        }
        .invoice-details h3 {
            margin: 0 0 15px 0;
            color: #2d3748;
            font-size: 16px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 4px 0;
        }
        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }
        .detail-value {
            color: #2d3748;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            margin: 25px 0;
            padding: 15px;
            background-color: #edf2f7;
            border-radius: 6px;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }
        .action-button:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        .footer {
            background-color: #edf2f7;
            padding: 25px 30px;
            text-align: center;
            font-size: 14px;
            color: #718096;
        }
        .footer p {
            margin: 5px 0;
        }
        .attachment-note {
            background-color: #fff5f5;
            border: 1px solid #fed7d7;
            color: #c53030;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 20px 15px;
            }
            .header {
                padding: 20px 15px;
            }
            .detail-row {
                flex-direction: column;
                margin-bottom: 12px;
            }
            .detail-value {
                font-weight: 600;
                margin-top: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $companyName }}</h1>
            <p>Invoice #{{ $invoice->invoice_number }}</p>
        </div>

        <div class="content">
            <div class="greeting">
                Hello {{ $client->name }},
            </div>

            <p>Thank you for your business! Please find attached your invoice for the services provided.</p>

            <div class="invoice-details">
                <h3>Invoice Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Invoice Number:</span>
                    <span class="detail-value">#{{ $invoice->invoice_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Issue Date:</span>
                    <span class="detail-value">{{ $invoice->issue_date->format('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value">{{ $dueDate }}</span>
                </div>
                @if($invoice->notes)
                <div class="detail-row">
                    <span class="detail-label">Notes:</span>
                    <span class="detail-value">{{ $invoice->notes }}</span>
                </div>
                @endif
            </div>

            <div class="total-amount">
                Total Amount: {{ $formattedTotal }}
            </div>

            <div class="attachment-note">
                📎 <strong>PDF Attachment:</strong> Your detailed invoice is attached to this email as a PDF document.
            </div>

            @if($invoice->terms)
            <div style="margin: 25px 0; padding: 20px; background-color: #f7fafc; border-radius: 6px;">
                <h4 style="margin: 0 0 10px 0; color: #2d3748;">Payment Terms</h4>
                <p style="margin: 0; color: #4a5568; font-size: 14px;">{{ $invoice->terms }}</p>
            </div>
            @endif

            <p>If you have any questions about this invoice, please don't hesitate to contact us.</p>

            <p>Best regards,<br>
            <strong>{{ $companyName }}</strong></p>
        </div>

        <div class="footer">
            <p><strong>{{ $companyName }}</strong></p>
            <p>This is an automated message. Please do not reply directly to this email.</p>
            <p>If you need assistance, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
