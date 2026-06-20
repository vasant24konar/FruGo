<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background:#f4f7f0; margin:0; padding:0; }
    .wrapper { max-width:560px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.07); }
    .header { background:#86B817; padding:28px 32px; text-align:center; }
    .header h1 { color:#fff; margin:0; font-size:26px; letter-spacing:1px; }
    .header p { color:rgba(255,255,255,.85); margin:4px 0 0; font-size:14px; }
    .body { padding:36px 40px; }
    .otp-box { text-align:center; margin:28px 0; padding:20px; background:#f8fdf0; border:2px dashed #86B817; border-radius:10px; }
    .otp-code { font-size:42px; font-weight:700; letter-spacing:10px; color:#86B817; }
    .footer { background:#f4f7f0; padding:20px 32px; text-align:center; font-size:12px; color:#888; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>🍎 FruGo</h1>
        <p>Fresh Fruits &amp; Vegetables</p>
    </div>
    <div class="body">
        <h2 style="margin:0 0 8px;color:#222;font-size:20px;">Your Login OTP</h2>
        <p style="color:#555;line-height:1.6;margin:0 0 16px;">
            You requested a one-time login code for <strong>{{ $recipientEmail }}</strong>.
            Use the code below to sign in:
        </p>
        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
            <p style="margin:8px 0 0;font-size:13px;color:#666;">Expires in <strong>10 minutes</strong></p>
        </div>
        <p style="color:#555;font-size:14px;line-height:1.6;">
            If you did not request this, you can safely ignore this email. Someone may have entered your email address by mistake.
        </p>
        <hr style="border:none;border-top:1px solid #eee;margin:24px 0;">
        <p style="color:#888;font-size:13px;margin:0;">
            For your security, this OTP is valid for one use only and expires after 10 minutes.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} FruGo. All rights reserved.<br>
        Fresh fruits &amp; vegetables delivered to your door.
    </div>
</div>
</body>
</html>
