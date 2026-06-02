<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Verification Code</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2c3e50; color: #fff; padding: 16px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { border: 1px solid #e9ecef; border-top: none; padding: 24px; border-radius: 0 0 8px 8px; }
        .code { font-size: 2rem; font-weight: bold; letter-spacing: 0.35rem; text-align: center; margin: 24px 0; color: #2c3e50; }
        .footer { margin-top: 24px; font-size: 0.9rem; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{ \App\Models\SystemSetting::getValue('site_name', config('app.name')) }}</strong><br>
        Admin Sign-In Verification
    </div>
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        <p>Use this one-time code to complete your admin sign-in:</p>
        <div class="code">{{ $code }}</div>
        <p>This code expires in <strong>10 minutes</strong>. If you did not attempt to sign in, contact support immediately.</p>
        <p class="footer">Do not share this code with anyone.</p>
    </div>
</body>
</html>
