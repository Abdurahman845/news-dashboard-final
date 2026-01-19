# News Dashboard Laravel Backend

Laravel REST API replacement for the legacy `backend/api.php`. Uses SQLite, token-based auth, and matches the existing React client contract (`http://localhost:8000/api`).

## Prerequisites
- PHP 8.2+, Composer, SQLite3.
- Node 18+ for the React client (unchanged).

## Backend Setup
1) `cd laravel-backend`
2) Copy `.env.example` to `.env` if needed, ensure:
   - `APP_URL=http://localhost:8000`
   - `DB_CONNECTION=sqlite`
   - `DB_DATABASE` points to `database/database.sqlite` (adjust path if you move the repo)
   - Optional: set `NEWS_API_KEY`, `AUTO_FETCH_ENABLED`, `AUTO_FETCH_CATEGORY`, `AUTO_FETCH_COUNT`
3) Ensure the database file exists: `touch database/database.sqlite`
4) Install deps: `composer install`
5) Generate key (only if .env was recreated): `php artisan key:generate`
6) Run migrations + seed sample data: `php artisan migrate --seed`
7) Serve API: `php artisan serve --port=8000`

**Reusing existing data:** copy `../backend/news.db` over `database/database.sqlite` before step 6; migrations will skip already-present tables.

## API Routes (JSON)
- `GET /api` → health/message
- `POST /api/register` `{name, email, password, password_confirmation}`
- `POST /api/login` `{email, password}`
- `GET /api/user` (Bearer token)
- `POST /api/logout` (Bearer token)
- `GET /api/articles` `?search=&category=&per_page=&page=` (published only)
- `GET /api/articles/{id}`
- `POST /api/articles` (Bearer) `{title, content, category_id, excerpt?, image_url?, published_at?}`
- `PUT /api/articles/{id}` (Bearer, owner only) same fields as POST, all optional
- `DELETE /api/articles/{id}` (Bearer, owner only)
- `GET /api/categories`
- `GET /api/categories/{id}` (includes published articles in that category)
- `GET /api/auto-fetch-news` `?category=&count=&api_key=` (uses `NEWS_API_KEY` if no query key)
- `POST /api/fetch-news` `{category?, count?, api_key?}` (requires API key or auth token)

Default seeded user: `admin@newsdashboard.com` / `password123`.

## CORS
Allowed origins: `http://localhost:3000` and `http://127.0.0.1:3000`.

## Frontend (unchanged)
1) `cd client`
2) `npm install`
3) `npm start` (runs on port 3000; base URL already points to `http://localhost:8000/api`)
