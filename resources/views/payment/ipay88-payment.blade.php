<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - iPay88</title>
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
            max-width: 450px;
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
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
            margin-bottom: 8px;
            font-weight: 600;
        }
        .subtitle {
            color: #718096;
            font-size: 14px;
        }
        .payment-details {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .detail-label {
            color: #718096;
            font-size: 14px;
        }
        .detail-value {
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
        }
        .amount-row {
            background: #667eea;
            color: white;
            padding: 16px;
            border-radius: 8px;
            margin-top: 12px;
        }
        .amount-row .detail-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
        }
        .amount-row .detail-value {
            color: white;
            font-size: 24px;
            font-weight: 700;
        }
        .payment-btn {
            width: 100%;
            background: #667eea;
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .payment-btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }
        .payment-btn:active {
            transform: translateY(0);
        }
        .security-note {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .security-icon {
            display: inline-flex;
            align-items: center;
            color: #48bb78;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .security-icon svg {
            width: 16px;
            height: 16px;
            margin-right: 6px;
            fill: currentColor;
        }
        .security-text {
            font-size: 12px;
            color: #a0aec0;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                </svg>
            </div>
            <h1>Complete Your Payment</h1>
            <p class="subtitle">Review your payment details below</p>
        </div>

        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Order Number</span>
                <span class="detail-value">{{ $orderNo }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Gateway</span>
                <span class="detail-value">iPay88</span>
            </div>

            <div class="amount-row">
                <div class="detail-row">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value">{{ $currency }} {{ $amount }}</span>
                </div>
            </div>
        </div>

        <!-- iPay88 Payment Form -->
        <form id="ipay88Form" method="POST" action="{{ $paymentUrl }}">
            @foreach($paymentData as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach

            <button type="submit" class="payment-btn" id="paymentBtn">
                <span class="btn-text">Proceed to Payment Gateway</span>
                <span class="btn-loading" style="display: none;">Processing...</span>
            </button>
        </form>

        <div class="security-note">
            <div class="security-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                </svg>
                Secure Payment
            </div>
            <p class="security-text">
                Redirecting to iPay88 payment gateway automatically...
            </p>
        </div>
    </div>

    <script>
        // Auto-submit form after page loads
        window.onload = function() {
            console.log('[iPay88] Page loaded, auto-submitting in 1.5 seconds...');
            setTimeout(function() {
                console.log('[iPay88] Submitting payment form to: {{ $paymentUrl }}');
                document.getElementById('ipay88Form').submit();
            }, 1500); // Wait 1.5 seconds before auto-submit
        };
    </script>
</body>
</html>
