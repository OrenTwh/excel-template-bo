<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            color: #333;
        }
        .header, .footer {
            text-align: center;
        }
        .header img {
            max-width: 150px;
            margin-bottom: 20px;
        }
        h1, h2 {
            margin: 0;
            padding: 0;
        }
        .content {
            margin: 20px 0;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
        }
        .content th, .content td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .content th {
            background-color: #f9f9f9;
        }
        .summary {
            margin-top: 20px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary th, .summary td {
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Company Logo">
        <h1>Invoice</h1>
        <h2>Reference: {{ $data->reference }}</h2>
    </div>

    <div class="content">
        <table>
            <tr>
                <th>Customer Name</th>
                <td>{{ $data->customer->fullname }}</td>
            </tr>
            <tr>
                <th>Customer Email</th>
                <td>{{ $data->customer->email }}</td>
            </tr>
            <tr>
                <th>Salesman</th>
                <td>{{ $data->salesman->fullname }}</td>
            </tr>
            <tr>
                <th>Warehouse</th>
                <td>{{ $data->warehouse->title }}</td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td>{{ $data->remarks }}</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <h3>Invoice Summary</h3>
        <table>
            <tr>
                <th>Order Tax</th>
                <td>RM {{ number_format($data->order_tax, 2) }}</td>
            </tr>
            <tr>
                <th>Order Discount</th>
                <td>RM {{ number_format($data->order_discount, 2) }}</td>
            </tr>
            <tr>
                <th>Shipping Cost</th>
                <td>RM {{ number_format($data->shipping_cost, 2) }}</td>
            </tr>
            <tr>
                <th>Final Amount</th>
                <td><strong>RM {{ number_format($data->final_amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Thank you for your support!</p>
    </div>
</body>
</html>
