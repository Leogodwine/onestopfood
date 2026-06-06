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
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run a queue worker if using database queues:

```bash
php artisan queue:work --tries=3
```

## Public file storage

Uploaded files (meals, verification documents, avatars) are stored under `storage/app/public/`.

**Required on every deploy:**

```bash
php artisan storage:link
```

This creates `public/storage` → `storage/app/public`, so URLs like `/storage/verifications/file.pdf` work.

**Verify on the server:**

```bash
ls -la public/storage
# Should be a symlink to .../storage/app/public
ls public/storage/verifications/
# Should list uploaded PDFs/images
```

**Uploaded verification files:** Admins and document owners can open files via authenticated routes (no `public/storage` symlink required):

- `GET /documents/verifications/{id}` — row in `user_verification_documents`
- `GET /documents/users/{userId}/profiles/{field}` — profile uploads (`selfie`, `proof-of-kitchen`, `kitchen-photo-1`, etc.)

Meal photos are served via `/meals/{id}/image` (no `public/storage` symlink required). Run `storage:link` only if you prefer direct `/storage/meals/...` URLs.

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

## Maintenance mode

Admins can enable maintenance from **Admin → System Monitoring**. Visitors see a “We will be back soon” page (HTTP 503).

### Before you enable maintenance

Set a fixed bypass path in `.env` so you can always reach the admin panel:

```env
APP_MAINTENANCE_SECRET=admin-bypass-change-me
```

After enabling maintenance, visit once:

`https://www.your-domain.com/admin-bypass-change-me`

That sets a cookie so you can browse, log in, and open **System Monitoring → Disable Maintenance Mode** to bring the site back for everyone.

If you did not set a secret, the bypass URL is shown on the System Monitoring page while you are still logged in, and in the success message when maintenance is turned on.

### If you are locked out (503 everywhere)

From the server project directory:

```bash
php artisan up
```

Or delete `storage/framework/down`. Either action turns maintenance off immediately.

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
