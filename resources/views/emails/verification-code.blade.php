<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .verification-code {
            background-color: #f8f9fa;
            border: 2px dashed #007bff;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
            border-radius: 8px;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 5px;
            font-family: 'Courier New', monospace;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{-- Add your logo here --}}
            {{-- <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo"> --}}
            <h1>
                @switch($purpose)
                    @case('registration')
                        Complete Your Registration
                        @break
                    @case('password_reset')
                        Reset Your Password
                        @break
                    @case('login')
                        Login Verification
                        @break
                    @default
                        Email Verification
                @endswitch
            </h1>
        </div>

        <div class="content">
            <p>Hello,</p>
            
            <p>
                @switch($purpose)
                    @case('registration')
                        Thank you for signing up! Please use the verification code below to complete your registration.
                        @break
                    @case('password_reset')
                        You requested to reset your password. Please use the verification code below to proceed.
                        @break
                    @case('login')
                        For your security, please use the verification code below to complete your login.
                        @break
                    @default
                        Please use the verification code below to verify your email address.
                @endswitch
            </p>

            <div class="verification-code">
                <p style="margin: 0; font-size: 18px; margin-bottom: 10px;">Your Verification Code:</p>
                <div class="code">{{ $code }}</div>
            </div>

            <div class="warning">
                <strong>Important:</strong> This code will expire in {{ $expiryMinutes }} minutes. 
                Please do not share this code with anyone for your security.
            </div>

            <p>If you didn't request this verification code, please ignore this email or contact our support team.</p>
        </div>

        <div class="footer">
            <p>
                This is an automated message, please do not reply to this email.<br>
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>