<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        /* ===== ScholarshipHub Glassmorphism Email ===== */
        body {
            margin: 0;
            padding: 0;
            background: #0f172a; /* dark slate */
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #e2e8f0;
            line-height: 1.6;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        .container {
            max-width: 480px;
            margin: 40px auto;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 1.5rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            padding: 30px 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .logo span {
            background: linear-gradient(135deg, #60a5fa, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .body {
            padding: 30px;
        }
        h2 {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 10px;
            color: #ffffff;
        }
        p {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            margin: 0 0 20px;
        }
        .code-container {
            background: rgba(59,130,246,0.15);
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 1rem;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        .code {
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 6px;
            color: #ffffff;
            font-family: 'Courier New', monospace;
            user-select: all;
            -webkit-user-select: all;
            -moz-user-select: all;
        }
        .copy-hint {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            margin-top: 10px;
        }
        .footer {
            padding: 20px 30px;
            background: rgba(0,0,0,0.2);
            text-align: center;
            font-size: 12px;
            color: rgba(255,255,255,0.4);
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .footer a {
            color: #60a5fa;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            .container {
                margin: 20px 10px;
            }
            .code {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"><span>Scholarship</span>Hub</div>
        </div>
        <div class="body">
            <h2>Verify Your Email Address</h2>
            <p>You requested to change the email address associated with your ScholarshipHub account. Use the code below to confirm this change.</p>

            <div class="code-container">
                <div class="code">{{ $code }}</div>
                <div class="copy-hint">Select and copy the code above, or type it into the verification form.</div>
            </div>

            <p style="font-size:12px; color: rgba(255,255,255,0.4);">
                This code will expire in 15 minutes. If you didn't request this change, please ignore this email or contact
                <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
            </p>
        </div>
        <div class="footer">
            © {{ date('Y') }} ScholarshipHub. All rights reserved.
        </div>
    </div>
</body>
</html>