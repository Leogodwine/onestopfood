<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: #fff; padding: 16px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { border: 1px solid #e9ecef; border-top: none; padding: 24px; border-radius: 0 0 8px 8px; }
        .code { font-size: 2rem; font-weight: bold; letter-spacing: 0.35rem; text-align: center; margin: 24px 0; color: #28a745; }
        .footer { margin-top: 24px; font-size: 0.9rem; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{ \App\Models\SystemSetting::getValue('site_name', config('app.name')) }}</strong><br>
        Account Verification
    </div>
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        <p>Use this one-time verification code to confirm your account:</p>
        <div class="code">{{ $code }}</div>
        <p>This code expires in <strong>{{ \App\Services\SocialSignupService::OTP_EXPIRY_MINUTES }} minutes</strong>. If you did not request this, you can ignore this email.</p>
        <p class="footer">Social sign-in accounts do not use a password. Always verify with this code when completing sign-up.</p>
    </div>
</body>
</html>
