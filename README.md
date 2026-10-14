# Sobur — portfolio

A single-page portfolio for **Sobur**, a graphic designer and software engineer.
Built on plain **PHP 8.1+ (no framework)**, **MySQL/MariaDB**, and **Apache**, with
hand-written CSS and a small vanilla JS file. No build step, no npm.

Visitors see who Sobur is, the best work (pulled live from the database), and can
get in touch through a working contact form. There is also a small admin area to
manage projects and read messages.

---

## Features

- One-page marketing site with smooth-scroll navigation and an accessible mobile menu.
- Projects are read from MySQL — adding work never means editing PHP markup.
- Per-project brand colors drive each card and its HTML/CSS mockup.
- Working contact form: saves to the database, emails Sobur, CSRF + honeypot +
  per-IP rate limiting, with a no-JS fallback.
- Admin area: log in, manage/reorder/feature projects, read and delete messages.
- Security: PDO prepared statements only, output escaping, CSRF everywhere,
  session hardening, brute-force lockout, security headers, gzip/cache.
- SEO: title/description, Open Graph, favicon, `robots.txt`, `sitemap.xml`.
- Accessibility: semantic landmarks, one `h1`, focus states, `prefers-reduced-motion`.

---

## Requirements

- PHP **8.1+** with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`
- MySQL 5.7+ / MariaDB 10.2+ / MySQL 8
- Apache with `mod_rewrite`, `mod_headers`, `mod_deflate`, `mod_expires`

---

## Project structure

```
.
├── public/              # web root (index.php, contact.php, assets, favicon, robots, sitemap)
├── includes/            # helpers.php, mailer.php, header.php, footer.php, partials/
├── config/              # config.example.php, config.php (gitignored)
├── database/            # schema.sql, seed.sql, create-admin.php
├── admin/               # login, dashboard, projects, messages
├── logs/                # error.log (gitignored)
├── docker/              # Dockerfile + Apache vhost
├── .htaccess            # forwards into /public, blocks private folders
├── router.php           # dev router for `php -S`
└── docker-compose.yml
```

---

## Local setup — XAMPP / Laragon / MAMP

1. **Place the project** in your web root, e.g. `C:\xampp\htdocs\sobur`
   (Laragon: `C:\laragon\www\Sobur`).
2. **Create the config**:
   ```
   cp config/config.example.php config/config.php
   ```
   Edit `config/config.php` and set the database credentials and `site.url`.
   For Laragon/XAMPP the defaults (`root`, empty password, `127.0.0.1:3306`) usually work.
3. **Create the database and tables**:
   ```
   mysql -u root -p < database/schema.sql
   mysql -u root -p sobur_portfolio < database/seed.sql
   ```
   (Or import both files through phpMyAdmin.)
4. **Create your admin account** (no default password ships):
   ```
   php database/create-admin.php your_username "a-strong-password"
   ```
5. **Open the site**: `http://localhost/sobur/` (or your vhost).
   Admin: `http://localhost/sobur/admin/`.

> The project root is used as the document root. The included `.htaccess` files
> rewrite requests into `/public` and deny access to `/config`, `/database`,
> `/includes` and `/logs`. If you can point the document root directly at
> `/public`, the site works the same way — just make sure `/admin` is still
> reachable (e.g. via an Apache `Alias`).

### Quick run without Apache (PHP built-in server)

```
php -S 127.0.0.1:8000 router.php
```
Then open `http://127.0.0.1:8000/`.

---

## Docker

```
docker compose up --build
```

- Site: **http://localhost:8080**
- MySQL is exposed on **localhost:3307** (root password `root`, app user `sobur` / `sobur_secret`).
- The schema and seed are imported automatically on first run.
- Create the first admin inside the web container:
  ```
  docker compose exec web php database/create-admin.php admin "a-strong-password"
  ```

The `web` service passes DB and site settings to PHP through environment
variables (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `SITE_URL`), which
`config.php` picks up automatically.

---

## Deploying to cPanel (shared host)

1. Upload all files to your account, e.g. `~/sobur` (or into `public_html`).
2. Create a MySQL database and user in cPanel, then import `database/schema.sql`
   and `database/seed.sql` via phpMyAdmin. Note the database name/prefix and user.
3. Create `config/config.php` from the example and set:
   - `db.host` (usually `localhost`), `db.name`, `db.user`, `db.pass`
   - `site.url` to your real domain (e.g. `https://sobur.com`)
   - `mail.method` to `smtp` and the SMTP settings (see below)
   - `env` to `production`
4. Point the domain's document root at the `public/` folder if your host allows it.
   If it does not, leave the root `.htaccess` in place — it forwards everything
   into `public/` and keeps the private folders blocked.
5. Ensure `logs/` is writable so PHP can write `error.log`.
6. Create your admin: via cPanel "Terminal" run
   `php database/create-admin.php your_username "a-strong-password"`,
   or run it locally against the same database.
7. Update the domain in `public/robots.txt` and `public/sitemap.xml`.

---

## Email

By default the contact form uses PHP `mail()`. Shared hosts usually deliver it,
but for reliable delivery configure SMTP in `config/config.php`:

```php
'mail' => [
    'method'    => 'smtp',
    'host'      => 'smtp.your-provider.com',
    'port'      => 587,
    'user'      => 'you@your-domain.com',
    'pass'      => 'your-smtp-password',
    'secure'    => 'tls',            // 'tls' or 'ssl'
    'from'      => 'no-reply@your-domain.com',
    'from_name' => 'Sobur Portfolio',
],
```

Messages are always saved to the database, so nothing is lost even if email fails.

---

## Creating / resetting an admin

```
php database/create-admin.php <username> [password]
```
Re-running with an existing username resets that account's password.

---

## TODO placeholders you still need to fill in

Search the project for `TODO` to find these:

- [ ] **config/config.php** — database host/name/user/password (already filled for local Laragon; set for your host).
- [ ] **config/config.php** — SMTP credentials if you want reliable email (otherwise `mail()` is used).
- [ ] **config/config.php** — set `env` to `production` on the live server.
- [ ] **database/seed.sql** — replace the three `live_url` values with the real project URLs.
- [ ] **public/robots.txt** and **public/sitemap.xml** — replace `http://localhost:8000` with your domain.
- [ ] **public/assets/img/og-image.png** — replace with a 1200×630 social share image.
- [ ] **Logos / Branding / Social posts / Posters** tiles use CSS samples; set each
      tile's `image` field in `public/index.php` to a filename in `public/assets/img/`
      to use a real image instead.

---

## Security notes

- All queries use PDO prepared statements.
- All output is escaped with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Every form (contact + all admin actions) carries a CSRF token.
- Contact form: server-side validation, honeypot field, per-IP rate limiting,
  and CR/LF header-injection protection.
- Admin: `password_hash`/`password_verify`, `session_regenerate_id()` on login,
  `HttpOnly`/`SameSite` cookies, idle timeout, and lockout after repeated failures.
- `config/config.php` is gitignored; `config.example.php` documents every option.
- Security headers (CSP incl. Google Fonts, `nosniff`, `SAMEORIGIN`,
  `Referrer-Policy`) are set in `public/.htaccess`.
- Errors are hidden and logged in production (`env = production`).
