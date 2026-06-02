# Production deployment

Use this checklist when deploying One Stop Food Order & Delivery to a live server.

## Environment (`.env`)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.your-domain.com
APP_SHOW_DEV_HINTS=false

LOG_LEVEL=error

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

PASSWORD_MIN_LENGTH=10
PASSWORD_UNCOMPROMISED=true

AUTO_CONFIRM_PAYMENTS=false

MAIL_MAILER=smtp
# Configure real SMTP credentials

TRUSTED_PROXIES=<load-balancer-ip>
```

Never commit `.env`. Set strong `ADMIN_SEED_PASSWORD` / `SEED_USER_PASSWORD` if running seeders on production.

## Deploy commands

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run a queue worker if using database queues:

```bash
php artisan queue:work --tries=3
```

## HTTPS & cookies

- Serve the site over HTTPS only.
- `AppServiceProvider` forces HTTPS when `APP_ENV=production`.
- Session cookies use `secure` and `encrypt` by default in production.

## Payments

- Keep `AUTO_CONFIRM_PAYMENTS=false` (enforced in code when `APP_ENV=production`).
- Set mobile money env vars to production (`MPESA_ENV=production`, etc.) and public HTTPS callback URLs.

## OAuth

Set redirect URIs to your live domain, e.g.:

- `https://www.your-domain.com/auth/google/callback`
- `https://www.your-domain.com/auth/facebook/callback`

## Security behavior in production

| Feature | Production behavior |
|---------|---------------------|
| OTP / 2FA on screen | Hidden (`APP_SHOW_DEV_HINTS=false`) |
| Admin 2FA | Sent by email |
| Social signup OTP | Sent by email; codes not logged |
| Errors | Branded pages (404, 419, 500, 503); no stack traces |
| Passwords | Min 10 chars + breach check |

## Local testing like production

To test production behavior on your machine without deploying:

```env
APP_DEBUG=false
APP_SHOW_DEV_HINTS=false
PASSWORD_MIN_LENGTH=10
PASSWORD_UNCOMPROMISED=true
AUTO_CONFIRM_PAYMENTS=false
LOG_LEVEL=error
```

Keep `APP_ENV=local` if you use `http://127.0.0.1` (production env forces HTTPS URLs). Use ngrok with `APP_URL=https://…` to test OAuth and secure cookies.
