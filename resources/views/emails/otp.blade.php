{{-- resources/views/emails/otp.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verification Code</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', Arial, Helvetica, sans-serif;
      background: #F3F3E0;
      padding: 20px;
      line-height: 1.6;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    .email-container {
      max-width: 600px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(24, 59, 78, 0.12);
    }

    .header {
      background: linear-gradient(135deg, #183B4E 0%, #27548A 100%);
      padding: 50px 30px 40px;
      text-align: center;
      position: relative;
    }

    .logo-wrap {
      width: 140px;
      height: 140px;
      margin: 0 auto 20px;
      border-radius: 50%;
      background: #ffffff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 1;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
      border: 4px solid rgba(221, 168, 83, 0.6);
      box-sizing: border-box;
      padding: 15px;
    }

    .logo {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
      border-radius: 50%;
    }

    .header-title {
      color: #ffffff;
      font-size: 24px;
      font-weight: 700;
      margin-top: 16px;
      position: relative;
      z-index: 1;
      text-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }

    .header-subtitle {
      color: rgba(255,255,255,0.9);
      font-size: 14px;
      font-weight: 400;
      margin-top: 8px;
      letter-spacing: 1px;
    }

    .content {
      padding: 40px 30px;
      text-align: left;
    }

    .greeting {
      font-size: 18px;
      color: #183B4E;
      margin-bottom: 20px;
      font-weight: 600;
    }

    .message {
      font-size: 16px;
      color: #555555;
      margin-bottom: 30px;
      line-height: 1.7;
    }

    .code-container {
      background: linear-gradient(135deg, #fffaf3 0%, #ffffff 100%);
      border: 2px dashed #DDA853;
      border-radius: 12px;
      padding: 25px;
      text-align: center;
      margin: 30px 0;
      position: relative;
    }

    .code-label {
      font-size: 14px;
      color: #183B4E;
      margin-bottom: 8px;
      font-weight: 500;
    }

    .verification-code {
      font-size: 36px;
      font-weight: 700;
      color: #183B4E;
      letter-spacing: 6px;
      margin: 10px 0;
    }

    .expiry-notice {
      background: #fff9f5;
      border-left: 4px solid #DDA853;
      padding: 15px 20px;
      border-radius: 0 8px 8px 0;
      margin: 25px 0;
      font-size: 14px;
      color: #555555;
    }

    .expiry-notice strong {
      color: #183B4E;
    }

    .signature {
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #e5e7eb;
      color: #555555;
      font-size: 16px;
    }

    .signature strong {
      color: #183B4E;
    }

    .footer {
      background: #F3F3E0;
      padding: 25px 30px;
      text-align: center;
      border-top: 1px solid #f0e6dc;
    }

    .footer-text {
      font-size: 13px;
      color: #888888;
      line-height: 1.5;
    }

    .security-tip {
      background: #ffffff;
      border: 1px solid #DDA853;
      border-radius: 8px;
      padding: 15px;
      margin-top: 15px;
      font-size: 12px;
      color: #555555;
    }

    .security-tip strong {
      color: #183B4E;
    }

    /* Larger circular logo variant (keeps parity with original design) */
    .logo-wrap.large {
      width: 240px;
      height: 240px;
      margin: 0 auto 20px;
      border-radius: 50%;
      background: #ffffff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 1;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.16);
      border: 6px solid rgba(255,255,255,0.85);
      box-sizing: border-box;
      padding: 12px;
    }

    .logo.large {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: contain;
      display: block;
      box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    @media (max-width: 600px) {
      body { padding: 10px; }
      .header { padding: 40px 20px 30px; }
      .content { padding: 30px 20px; }
      .header-title { font-size: 20px; }
      .verification-code { font-size: 28px; letter-spacing: 4px; }
      .logo-wrap, .logo-wrap.large { width: 110px; height: 110px; padding: 12px; }
      .logo, .logo.large { border-radius: 50%; }
      .footer { padding: 20px; }
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <!-- If you want the bigger circular logo like the original, add the "large" class to logo-wrap and logo -->
      <div class="logo-wrap large">
        <img src="{{ $message->embed(public_path('images/logo.png')) }}"
             alt="Academia Plus"
             class="logo large">
      </div>

      <h1 class="header-title">Academia Plus</h1>
      <p class="header-subtitle">Verification Code</p>
    </div>

    <div class="content">
      <div class="greeting">
        Hello {{ $user->name }},
      </div>

      <div class="message">
        You have requested a verification code for <strong>Academia Plus</strong> — your online learning platform for courses across many fields.
        Please use the code below to complete your verification and continue to access your courses and account settings.
      </div>

      <div class="code-container">
        <div class="code-label">Your Verification Code</div>
        <div class="verification-code">{{ $code }}</div>
      </div>

      <div class="expiry-notice">
        <strong>Important:</strong> This code will expire in 10 minutes. For your safety, do not share this code with anyone. If you did not request this code, you can ignore this email.
      </div>

      <div class="signature">
        Best regards,<br>
        <strong>Academia Plus Team</strong>
      </div>
    </div>

    <div class="footer">
      <div class="footer-text">
        This email was sent automatically by Academia Plus. Please do not reply to this message.
      </div>

      <div class="security-tip">
        <strong>Security Tip:</strong> Never share your verification code. Academia Plus will never ask you to provide this code over the phone or by email.
      </div>
    </div>
  </div>
</body>
</html>
