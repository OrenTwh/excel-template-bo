<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Payment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 420px;
            width: 100%;
        }
        .badge {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 20px;
        }
        h1 { font-size: 22px; color: #2d3748; margin-bottom: 6px; }
        .subtitle { font-size: 14px; color: #718096; margin-bottom: 28px; }
        .detail-box {
            background: #f7fafc;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 28px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 6px 0;
        }
        .row:not(:last-child) { border-bottom: 1px solid #e2e8f0; }
        .label { color: #718096; }
        .value { font-weight: 600; color: #2d3748; }
        .value.total { font-size: 18px; color: #3182ce; }
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.88; }
        .btn-success { background: #48bb78; color: white; margin-bottom: 12px; }
        .btn-cancel  { background: #fed7d7; color: #c53030; }
    </style>
</head>
<body>
    <div class="container">
        <div class="badge">Mock Payment</div>
        <h1>Simulate Payment</h1>
        <p class="subtitle">No real payment gateway is configured. Use the buttons below to simulate a result.</p>

        <div class="detail-box">
            <div class="row">
                <span class="label">Order No.</span>
                <span class="value">{{ $orderNo }}</span>
            </div>
            <div class="row">
                <span class="label">Total</span>
                <span class="value total">{{ $currency }} {{ $amount }}</span>
            </div>
        </div>

        <a href="{{ $successUrl }}" class="btn btn-success">Simulate Payment Success</a>
        <a href="{{ $cancelUrl }}"  class="btn btn-cancel">Simulate Payment Cancel</a>
    </div>
</body>
</html>
