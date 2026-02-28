# PromptShare (PHP + MySQL MVC)

A deployment-ready prompt sharing web application built with **PHP 8+**, **MySQL**, and an MVC architecture (no Node.js required).

## Features
- User registration/login with bcrypt hashed passwords
- Session auth + role-based authorization (`user`, `super_admin`)
- Google OAuth integration points (`/auth/google`, callback scaffold)
- Submit prompts with image upload + compression
- Moderation workflow: pending/approved/rejected
- Prompt interactions: likes, saves, copies, unique session views
- User dashboard with Instagram-style card grid and analytics counters
- Super Admin dashboard for moderation, users, and global analytics
- Public SEO-friendly prompt pages (`/prompt/{slug}`)
- Open Graph tags and meta description support
- Lazy loading prompt grid via AJAX
- CSRF protection, XSS escaping, PDO prepared statements
- Basic file cache for homepage query

## Project Structure
```
/app
  /controllers
  /core
  /models
  /views
/config
/routes
/database
/public
  /assets
  index.php
.htaccess
```

## Setup
1. Create a MySQL database (e.g. `promptshare`).
2. Import schema:
   ```bash
   mysql -u root -p promptshare < database/schema.sql
   ```
3. Update database and app config in `config/config.php`.
4. Point Apache document root to `public/` (or keep root and use provided root `.htaccess`).
5. Ensure `public/assets/uploads` and `storage/cache` are writable:
   ```bash
   chmod -R 775 public/assets/uploads storage/cache
   ```
6. Open app in browser.

## Important Files
- `public/index.php` — Front controller + router dispatcher
- `routes/web.php` — Application routes and middleware assignment
- `database/schema.sql` — Normalized schema with FKs + indexes
- `app/controllers/PromptController.php` — prompt CRUD + interactions + upload handling
- `app/controllers/AdminController.php` — moderation and analytics flow
- `app/models/Prompt.php` — optimized prompt retrieval and aggregates
- `app/models/Interaction.php` — likes/saves/copies/views tracking
- `app/views/layouts/main.php` — SEO/OG meta + global navigation

## Shared Hosting (cPanel) Notes
- No build pipeline is required.
- Upload files directly and configure DB credentials.
- If `public/` cannot be web root, keep root `.htaccess` redirect enabled.
- Google OAuth can be enabled by filling `google_oauth` keys in `config/config.php`.
