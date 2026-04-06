<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Payment Successful' : 'Payment Cancelled' }}</title>
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
            text-align: center;
        }
        .icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            background: {{ $success ? '#c6f6d5' : '#fed7d7' }};
        }
        h1 { font-size: 22px; color: #2d3748; margin-bottom: 8px; }
        .message { font-size: 14px; color: #718096; margin-bottom: 28px; }
        .detail-box {
            background: #f7fafc;
            border-radius: 8px;
            padding: 16px 20px;
            text-align: left;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">{{ $success ? '✓' : '✕' }}</div>
        <h1>{{ $success ? 'Payment Successful' : 'Payment Cancelled' }}</h1>
        <p class="message">{{ $message }}</p>

        <div class="detail-box">
            <div class="row">
                <span class="label">Order No.</span>
                <span class="value">{{ $orderNo }}</span>
            </div>
            <div class="row">
                <span class="label">Amount</span>
                <span class="value">{{ $currency }} {{ $amount }}</span>
            </div>
            <div class="row">
                <span class="label">Status</span>
                <span class="value">{{ $success ? 'Confirmed' : 'Cancelled' }}</span>
            </div>
        </div>
    </div>
</body>
</html>
