# StudyHub

StudyHub is a Laravel + Inertia React group-study platform with student and admin dashboards, study groups, shared resources, discussions, sessions, notifications, and seeded demo data.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js 22 or newer
- npm
- SQLite PHP extension enabled

## Quick Setup

Clone the repo, install everything, migrate the database, seed demo data, and build assets:

```bash
git clone <your-repo-url>
cd GROUPSTUDYHUB
composer run setup
composer dev
```

Open the app at:

```text
http://127.0.0.1:8000/studyhub
```

## Demo Accounts

Use these after running `composer run setup`:

```text
Student
Email: student@studyhub.test
Password: password
Role: Student

Admin
Email: admin@studyhub.test
Password: password
Role: Admin
```

Make sure the selected role matches the account. An admin account cannot log in as Student, and a student account cannot log in as Admin.

## Manual Setup

If you prefer to run each step yourself:

```bash
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
composer dev
```

On Windows PowerShell, use this instead of `cp`:

```powershell
Copy-Item .env.example .env
```

## Development

Run the full Laravel server, queue listener, and Vite dev server:

```bash
composer dev
```

Run only Vite:

```bash
npm run dev
```

Run checks before pushing:

```bash
npm run build
php artisan test
```

## Reset Local Data

To reset the database and recreate demo data:

```bash
composer run setup:fresh
```

This recreates the SQLite database tables and reseeds the demo users, groups, resources, discussions, and sessions.

## Notes For GitHub

The repo intentionally does not commit generated/local files such as:

- `.env`
- `vendor/`
- `node_modules/`
- `database/database.sqlite`
- `public/build/`
- `public/storage`

After cloning, run `composer run setup` to recreate those local files.
