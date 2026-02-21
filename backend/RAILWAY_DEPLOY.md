# Railway Deploy Guide (Backend)

## 1) Create backend service
- In Railway, create a new service from this repo.
- Set service root to `backend/`.

## 2) Environment variables
- Add all values from `.env.railway.example`.
- Important:
  - `APP_KEY` (generate locally with `php artisan key:generate --show`)
  - `APP_URL` to backend Railway domain
  - `FRONTEND_URL` to frontend domain
  - `GOOGLE_*` values
  - `STRIPE_*` values (if used)
  - `CORS_ALLOWED_ORIGINS` to frontend domain

## 3) Database
- Add Railway PostgreSQL plugin/service.
- Railway injects `DATABASE_URL` automatically.
- Keep `DB_CONNECTION=pgsql`.

## 4) Start command
- `Procfile` is included:
  - `web: php artisan serve --host=0.0.0.0 --port=${PORT}`

## 5) Run migrations on production
- From Railway service shell or one-time command:
  - `php artisan migrate --force`
  - optional seed: `php artisan db:seed --force`

## 6) Cache optimization
- Recommended after deploy:
  - `php artisan optimize`

## 7) Google OAuth production redirect
- In Google Cloud OAuth client:
  - Authorized redirect URI:
    - `https://<your-backend-domain>/api/google/callback`

## 8) CORS sanity check
- `config/cors.php` already reads `CORS_ALLOWED_ORIGINS`.
- Ensure it exactly matches frontend domain and protocol.

