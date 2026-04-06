<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to iPay88 Payment Gateway</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        h1 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        p {
            color: #718096;
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .order-info {
            background: #f7fafc;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .order-info p {
            margin: 0;
            color: #4a5568;
            font-size: 13px;
        }
        .order-info strong {
            color: #2d3748;
            font-weight: 600;
        }
        .spinner {
            width: 50px;
            height: 50px;
            margin: 0 auto 20px;
            border: 4px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .manual-submit {
            margin-top: 20px;
        }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .note {
            margin-top: 16px;
            font-size: 12px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
            </svg>
        </div>

        <h1>Redirecting to Payment Gateway</h1>
        <p>Please wait while we redirect you to iPay88 payment gateway to complete your transaction.</p>

        <div class="order-info">
            <p><strong>Order Number:</strong> {{ $orderNo }}</p>
        </div>

        <div class="spinner"></div>

        <p class="note">You will be redirected automatically in a few seconds...</p>

        <!-- iPay88 Payment Form -->
        <form id="ipay88Form" method="POST" action="{{ $paymentUrl }}" style="display: none;">
            @foreach($paymentData as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
        </form>

        <div class="manual-submit">
            <button type="button" class="btn" onclick="document.getElementById('ipay88Form').submit();">
                Click here if not redirected
            </button>
        </div>
    </div>

    <script>
        // Auto-submit form after page loads
        window.onload = function() {
            setTimeout(function() {
                document.getElementById('ipay88Form').submit();
            }, 1500); // Wait 1.5 seconds before auto-submit
        };
    </script>
</body>
</html>
