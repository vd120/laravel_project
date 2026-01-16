<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .tagline {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.6;
        }

        .code-container {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }

        .code-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 15px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .verification-code {
            font-family: 'Courier New', monospace;
            font-size: 32px;
            font-weight: bold;
            color: #333;
            letter-spacing: 6px;
            background-color: white;
            padding: 15px 25px;
            border-radius: 6px;
            border: 2px solid #667eea;
            display: inline-block;
            margin: 10px 0;
        }

        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .warning-text {
            color: #856404;
            font-weight: 600;
            margin: 0;
        }

        .instructions {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }

        .instructions h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }

        .instructions li {
            color: #666;
            margin-bottom: 5px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer-text {
            color: #6c757d;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .copyright {
            color: #adb5bd;
            font-size: 11px;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-container {
                border-radius: 0;
            }

            .header {
                padding: 30px 20px;
            }

            .content {
                padding: 30px 20px;
            }

            .greeting {
                font-size: 20px;
            }

            .verification-code {
                font-size: 24px;
                letter-spacing: 4px;
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">{{ config('app.name', 'Laravel') }}</div>
            <div class="tagline">Secure Email Verification</div>
        </div>

        <!-- Content -->
        <div class="content">
            <h1 class="greeting">Welcome, {{ $user->name }}! 🎉</h1>

            <p class="message">
                Thank you for joining {{ config('app.name') }}! To complete your registration and secure your account,
                please use the verification code below.
            </p>

            <!-- Verification Code -->
            <div class="code-container">
                <div class="code-label">Your Verification Code</div>
                <div class="verification-code">{{ $verificationCode }}</div>
            </div>

            <!-- Warning -->
            <div class="warning">
                <p class="warning-text">
                    ⚠️ <strong>Important:</strong> This code expires in 10 minutes.
                    If you don't verify, you can register again with the same email.
                </p>
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h3>How to Verify Your Account:</h3>
                <ol>
                    <li>Return to the verification page in your browser</li>
                    <li>Enter the 6-digit code shown above</li>
                    <li>Click "Verify Account" to complete registration</li>
                    <li>Start exploring {{ config('app.name') }}!</li>
                </ol>
            </div>

            <!-- CTA Button -->
            <div style="text-align: center;">
                <a href="{{ url('/email/verify') }}" class="cta-button">
                    🔓 Go to Verification Page
                </a>
            </div>

            <p style="text-align: center; color: #666; margin-top: 20px; font-size: 12px;">
                Didn't request this? You can safely ignore this email.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-text">
                You're receiving this email because you registered for {{ config('app.name') }}.
            </p>
            <p class="copyright">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
