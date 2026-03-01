# PromptShare

PromptShare is a PHP + MySQL prompt sharing platform where users can publish prompts, discover trending content, and interact through likes, saves, views, and copy tracking.

## What’s Included

- Authentication (register/login/logout) with role-aware navigation.
- Public discovery homepage with:
  - Full-text search (`q`)
  - Sorting filters (`newest`, `most liked`, `most saved`, `most viewed`)
  - Lazy-loading card grid
- Prompt details page with interactive actions:
  - Like (toggle)
  - Save (toggle)
  - Copy (counter)
  - Unique session views
- Frontend user dashboard (including super admins) for personal prompt activity.
- Admin dashboard for super admins (moderation + analytics).

## Tech Stack

- PHP 8+
- MySQL 8+
- Bootstrap 5
- Vanilla JavaScript
- PDO + prepared statements

## Project Structure

```text
app/
  controllers/
  core/
  models/
  views/
config/
database/
public/
  assets/
routes/
README.md
```

## Setup

1. Create the database:
   ```bash
   mysql -u root -p -e "CREATE DATABASE promptshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```
2. Import schema:
   ```bash
   mysql -u root -p promptshare < database/schema.sql
   ```
3. Update configuration in `config/config.php` (DB credentials, base URL, cache settings).
4. Ensure writable directories:
   ```bash
   mkdir -p storage/cache public/assets/uploads
   chmod -R 775 storage/cache public/assets/uploads
   ```
5. Serve the app from `public/` as document root.

## Local Run (PHP Built-in Server)

```bash
php -S 0.0.0.0:8000 -t public
```

Then open: `http://localhost:8000`

## Default Roles

- `user`
- `super_admin`

`/dashboard` is available to any authenticated user role.  
`/admin` is restricted to `super_admin`.

## Security Notes

- CSRF token checks for state-changing actions.
- Output escaping helpers are used in views.
- Passwords use bcrypt hashing.
- DB operations use prepared statements.

## Key Routes

- `GET /` — Home (filters + list)
- `GET /prompt/{slug}` — Prompt details
- `POST /prompts/like` — Toggle like
- `POST /prompts/save` — Toggle save
- `POST /prompts/copy` — Add copy event
- `GET /dashboard` — Frontend dashboard
- `GET /admin` — Admin dashboard

## Notes

Google OAuth endpoints are scaffolded and require additional implementation/configuration before production use.
