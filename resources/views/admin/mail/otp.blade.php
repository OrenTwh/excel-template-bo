<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP for Secure Access</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .logo-container {
            text-align: center; 
            margin-bottom: 20px;
        }
        .logo {
            max-width: 200px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
        }
        .otp-container {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            text-align: center;
        }
        .otp {
            font-size: 24px;
            font-weight: bold;
            color: #4a056b;
        }
        .message {
            margin-top: 10px;
            font-size: 16px;
            color: #555;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Company Logo" class="logo">
    </div>
    <h1>Your OTP for Secure Access</h1>

    <div class="otp-container">
        <p>Use the OTP below to complete your login or reset your password:</p>
        <div class="otp">{{ $data['otp_code'] }}</div>
        <p class="message">This OTP is valid for the next 10 minutes. Please do not share it with anyone.</p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply directly to this message.</p>
        <p>If you didn't request this OTP, please contact our support team immediately.</p>
    </div>
</body>
</html>
