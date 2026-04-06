<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            line-height: 1.6;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('admin/images/logo.png') }}" alt="Company Logo">
            <h2>Quotation Reference: {{ $data->reference }}</h2>
        </div>
        <div class="content">
            <p>Dear {{ $data->customer->email }},</p>
            <p>Thank you for your support! Please find your quotation attached below:</p>
            <p>
                <strong>Invoice Summary:</strong><br>
                <strong>Amount:</strong> RM {{ number_format($data->final_amount, 2) }}<br>
                <strong>Order Tax:</strong> RM {{ number_format($data->order_tax, 2) }}<br>
                <strong>Order Discount:</strong> RM {{ number_format($data->order_discount, 2) }}
            </p>
            <p>For any inquiries, feel free to contact us.</p>
        </div>
        <div class="footer">
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
