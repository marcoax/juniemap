### Juniemap

A Laravel 12 + Inertia v2 + React 19 + Tailwind CSS v4 application. Juniemap displays and manages map-based data using the Google Maps API.

- PHP: 8.3
- Laravel: 12
- Inertia (Laravel + React): v2
- React: 19
- Tailwind CSS: v4
- Node.js: 20+ recommended
- Database: MySQL (default)

---

### Features
- Inertia-powered SPA with React 19
- Tailwind v4 styling
- Google Maps integration (client + server keys supported)
- Session & queue using database drivers by default

---

### Quick Start

1) Clone and install dependencies
- Composer dependencies: `composer install`
- Node dependencies: `npm install`

2) Environment
- Copy `.env.example` to `.env` (or ensure `.env` exists)
- Set the database credentials:
  - `DB_DATABASE`
  - `DB_USERNAME`
  - `DB_PASSWORD`
- Set application URL (used by Vite/Inertia):
  - `APP_URL=http://juniemap.test` (adjust as needed)
- Provide Google Maps API keys (see Environment section below)

3) Generate app key
- `php artisan key:generate`

4) Database setup
- Create the database defined in `.env`
- Run migrations: `php artisan migrate`
- If using database sessions/queues (default), ensure tables are created:
  - Sessions table is included in migrations
  - Queues table is included in migrations

5) Development servers
- Backend: `php artisan serve`
- Frontend: `npm run dev`

Open the app at your configured `APP_URL`.

---

### Environment
Required variables in `.env` (most are present already):

- App basics
  - `APP_NAME=juniemap`
  - `APP_ENV=local`
  - `APP_URL=http://juniemap.test`

- Database
  - `DB_CONNECTION=mysql`
  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3306`
  - `DB_DATABASE=juniemap`
  - `DB_USERNAME=root`
  - `DB_PASSWORD=`

- Sessions / Cache / Queue
  - `SESSION_DRIVER=database`
  - `CACHE_STORE=database`
  - `QUEUE_CONNECTION=database`

- Google Maps API
  - Client-side key (exposed to Vite/React):
    - `VITE_GOOGLE_MAPS_API_KEY=your_public_browser_key`
  - Server-side key (if needed for server-side calls):
    - `GOOGLE_MAPS_API_KEY=your_server_key`

Tips:
- Restrict the client key by HTTP referrer; restrict the server key by IP.
- If maps don’t render, check browser console for API errors (quotas, restrictions, or missing libraries).

---

### Scripts
- Start dev servers: `npm run dev`
- Production build: `npm run build`
- Run tests: `php artisan test`
- Lint/format PHP (Laravel Pint): `vendor/bin/pint`

If frontend changes don’t show up, ensure `npm run dev` is running, or rebuild with `npm run build`.

---

### Testing
- PHP unit & feature tests: `php artisan test`
- Example present: `tests/Feature/DashboardTest.php`

Run specific tests for speed, for example:
- `php artisan test tests/Feature/DashboardTest.php`

---

### Code Style & Conventions
- PHP: Laravel Pint – run `vendor/bin/pint` before committing.
- Use Form Requests for validation, Eloquent relationships with return types, and eager loading to avoid N+1.
- Follow existing naming and structure conventions in the project.

---

### Frontend (Inertia + React)
- Pages live under `resources/js/pages` (Inertia v2)
- Components under `resources/js/components`
- Use `<Link>` or `router.visit()` for navigation
- Use Inertia form methods (`router.post`, etc.) instead of traditional form posts
- Tailwind v4: `@import "tailwindcss";` in CSS; avoid deprecated utilities

---

### Laravel 12 Notes
- No `app/Http/Middleware` directory by default; register middleware and routes in `bootstrap/app.php`
- Commands in `app/Console/Commands/` auto-register
- Use configuration via `config()`; avoid using `env()` directly outside config files

---

### Project Structure (high level)
- `bootstrap/app.php` – Application bootstrap, middleware, routes registration
- `config/` – Configuration files
- `resources/js/pages/` – Inertia React pages (e.g., `dashboard.tsx`, `welcome.tsx`)
- `resources/js/components/` – Reusable React components
- `resources/js/actions/` – Frontend route/controller abstractions used by the SPA
- `routes/` – HTTP, console, and (if present) API routes
- `tests/` – PHP unit/feature tests

---

### Troubleshooting
- Vite manifest error (Unable to locate file in Vite manifest): run `npm run dev` or `npm run build`
- Styling not applying: ensure Tailwind v4 is correctly imported and dev server is running
- Maps not showing: verify `VITE_GOOGLE_MAPS_API_KEY` is set, check referrer restrictions, and review browser console errors
- Database errors: confirm DB credentials and run `php artisan migrate`

---

### Deployment (basic outline)
- Set production `.env` (APP_KEY, APP_URL, DB_*, MAPS keys)
- Install dependencies: `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build`
- Run migrations: `php artisan migrate --force`
- Cache config/routes/views: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- Ensure queue worker and scheduler are configured if queues are used

---

### License
Add your license here (e.g., MIT).