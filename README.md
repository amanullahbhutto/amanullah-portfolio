# Amanullah - Laravel 11 Portfolio

A complete dynamic developer portfolio built with Laravel 11, Bootstrap 5, MySQL/SQLite, AOS animations, and Spatie Laravel Permission.

## Included features

- Responsive public pages: Home, Projects, Project Details, Blog Posts, Blog Details, Services, About Me, and Contact.
- Persistent day/night mode on the public website, authentication screens, and admin panel.
- Dynamic profile, experience, education, skills, services, projects, and blog content.
- Responsive Bootstrap 5 admin panel with active navigation, hover states, tables, forms, and mobile sidebar.
- Spatie roles and permissions with `admin` and `editor` roles.
- Secure login, registration, logout, CSRF protection, validation, password hashing, and session regeneration.
- Contact inbox with unread badge. Opening a message marks it as read, so the notification counter decreases automatically.
- Safe image uploads for profile, projects, and blog posts, plus a PDF CV upload.
- SQLite quick-start and MySQL-ready configuration.
- Seeded CV content, projects, services, posts, education, experience, skills, and administrator account.
- Feature tests for the homepage, contact workflow, admin access, and unread-message behaviour.

## Requirements

- PHP 8.2 or newer
- Composer 2
- Required PHP extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- SQLite extension for the quick setup, or MySQL 8+

## Quick setup with SQLite

Run these commands from the project folder:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

Open `http://127.0.0.1:8000`.

The Bootstrap 5, Bootstrap Icons, and AOS production files are already included in `public/vendor`, so no Node.js build is required to run the website.

## Default administrator

- Login URL: `http://127.0.0.1:8000/login`
- Email: `admin@gmail.com`
- Password: `12345678`

Change this password immediately before publishing the site. You can use Laravel Tinker:

```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'admin@gmail.com')->first();
$user->password = Hash::make('YOUR-STRONG-NEW-PASSWORD');
$user->save();
```

## MySQL setup

Create an empty MySQL database, then update `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amanullah_portfolio
DB_USERNAME=your_database_user
DB_PASSWORD=your_secure_password
```

Then run:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
```

## Roles and permissions

The database seeder creates:

- `admin`: all permissions, including user and role management.
- `editor`: portfolio content, profile, message, skill, education, and experience management. It cannot manage user roles.

New registrations receive the editor role. For production security, disable public registration after creating the accounts you need:

```dotenv
ALLOW_REGISTRATION=false
```

Then clear cached configuration:

```bash
php artisan config:clear
```

## Contact messages

Public submissions are validated, rate limited, protected with CSRF, checked by a hidden spam field, and stored in `contact_messages`.

The admin header and sidebar show the unread message count. A message remains new until an authorised admin/editor opens its detail page. Opening it records `read_at`, which reduces the counter automatically.

## Uploads

- Profile/project/post images: JPG, JPEG, PNG, or WebP, maximum 3 MB.
- CV: PDF, maximum 5 MB.
- Uploaded files are stored on the Laravel `public` disk.

Always run `php artisan storage:link` after deployment. Never expose `.env`, `storage/logs`, or the project root as the public web directory.

## Production deployment checklist

1. Create a full database and file backup.
2. Deploy to a staging environment first.
3. Point the web document root to the project `public/` directory.
4. Set `APP_ENV=production`, `APP_DEBUG=false`, the correct `APP_URL`, database values, and mail values.
5. Set a strong administrator password and `ALLOW_REGISTRATION=false`.
6. Run `composer install --no-dev --optimize-autoloader`.
7. Run `php artisan migrate --force`.
8. Run `php artisan storage:link`.
9. Run `php artisan optimize`.
10. Test login, contact submission, image uploads, navigation, responsive layouts, and both themes.
11. Configure HTTPS, server backups, log monitoring, and a cron entry for `php artisan schedule:run` if scheduled tasks are added.

Recommended permissions: directories `755`, files `644`, and writable access only for `storage` and `bootstrap/cache` according to your server user/group.

## Rollback plan

Before every production update:

1. Back up the database and `storage/app/public`.
2. Keep the previous release folder or ZIP.
3. Record any migrations included in the release.
4. If a problem occurs, enable maintenance mode with `php artisan down`, restore the previous files and database backup, run `php artisan optimize:clear`, verify the website, and then run `php artisan up`.

Do not run `migrate:rollback` on production unless the migration's data impact has been reviewed and a verified database backup exists.

## Tests

```bash
php artisan test
```

## Optional front-end asset refresh

The compiled vendor files are included. To update them later:

```bash
npm install
npm run build
```

This copies Bootstrap, Bootstrap Icons, and AOS from `node_modules` into `public/vendor`.
