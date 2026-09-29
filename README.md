# User Profile App (Register > Login > Profile)

Stack: HTML, CSS, JS (jQuery AJAX only), Bootstrap, PHP, MySQL (prepared statements),
MongoDB Atlas (profile details), Redis (backend sessions), browser localStorage (client session).

## Project structure
```
assests/  css/  js/ (api.js, login.js, profile.js, register.js, index.js)
php/      (config.php, config.local.php, login.php, profile.php, register.php, logout.php, health.php)
index.html  login.html  profile.html  register.html
Dockerfile  render.yaml  netlify.toml  composer.json  composer.lock
```

## Run locally (XAMPP) — unchanged from before
Your existing `php/config.local.php` still works as-is. Just run:
```
composer install
```
then open `http://localhost/<folder>/`.

## Hosted architecture
| Part | Service |
|---|---|
| Frontend (HTML/CSS/JS) | Netlify (`netlify.toml`) |
| PHP backend | Render, Docker (`Dockerfile`, `render.yaml`) |
| MySQL | Aiven for MySQL (free plan) — or any MySQL host |
| MongoDB | Already on MongoDB Atlas — reuse the same URI |
| Redis | Upstash Redis (or any Redis with TLS) |

### Backend environment variables (set in the Render dashboard, not in Git)
`ALLOWED_ORIGIN` (your Netlify URL, no trailing slash), `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`,
`DB_PASS`, `DB_SSL=true`, `MONGO_URI` (your existing Atlas string), `MONGO_DB_NAME`, `REDIS_HOST`,
`REDIS_PORT`, `REDIS_USERNAME`, `REDIS_PASSWORD`, `REDIS_TLS=true`.

### Frontend
Edit `PRODUCTION_API` in `js/api.js` to your Render URL (with trailing slash).

Note: free tiers sleep after inactivity, so the first request can take up to a minute.

## What changed from your local version
- Fixed 3 typos in `php/config.php` (`Cotent-Type` -> `Content-Type`, `json_ecode` -> `json_encode`,
  a stray message typo) that would have caused a fatal error on a failed MySQL connection.
- Added CORS handling so the Netlify frontend is allowed to call this backend.
- Settings now fall back to environment variables when `config.local.php` isn't present (Render doesn't use that file).
- Added `js/api.js` and updated the 9 AJAX calls across `login.js`, `register.js`, `profile.js` to use it.
