<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $headline }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #198754; color: #fff; padding: 16px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { border: 1px solid #e9ecef; border-top: none; padding: 24px; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; background: #198754; color: #fff !important; text-decoration: none; padding: 10px 18px; border-radius: 6px; margin-top: 16px; }
        .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee; font-size: 0.9rem; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{ $brand }}</strong><br>
        {{ $headline }}
    </div>
    <div class="content">
        <p>{{ $body }}</p>
        <a href="{{ $actionUrl }}" class="btn">{{ $actionLabel }}</a>
        <div class="footer">
            This message was sent by {{ $brand }}. If you did not request this, please contact support.
        </div>
    </div>
</body>
</html>
