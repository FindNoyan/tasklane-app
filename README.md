# Tasklane — Kanban Task Manager (Laravel)

A simple Kanban-style task board — To do / In progress / Done — with
priority markers and due dates. Built with Laravel and Blade, styled
with Tailwind CSS.

This folder contains the **custom application code only** (models,
migration, controller, routes, views). It's meant to be dropped into a
fresh Laravel install rather than run standalone, so you always get
the correct framework files for whatever Laravel version you install.

## Setup

1. **Create a fresh Laravel project** (this generates all the standard
   framework files):
   ```bash
   composer create-project laravel/laravel tasklane
   ```

2. **Copy these folders into the new `tasklane` project**, replacing
   files where asked:
   - `app/Models/Board.php`, `app/Models/Task.php`
   - `app/Http/Controllers/TaskController.php`
   - `database/migrations/*` (the two Tasklane migration files)
   - `database/seeders/DatabaseSeeder.php`
   - `routes/web.php`
   - `resources/views/layouts/app.blade.php`
   - `resources/views/tasks/index.blade.php`
   - `resources/css/app.css`
   - `tailwind.config.js`, `postcss.config.js` (add these; Laravel 10
     doesn't include Tailwind by default)

3. **Install the frontend dependencies** (Tailwind isn't included in
   Laravel by default):
   ```bash
   npm install -D tailwindcss postcss autoprefixer
   ```

4. **Set up the database** — SQLite is the fastest way to get running,
   no separate DB server needed. In your `.env` file:
   ```
   DB_CONNECTION=sqlite
   ```
   Then create the database file and run migrations + seed data:
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. **Run it:**
   ```bash
   npm run build
   php artisan serve
   ```
   Visit http://localhost:8000

## Why it's structured this way

Laravel projects are normally created with the official installer,
which generates dozens of framework boilerplate files (kernel,
middleware, config). Hand-copying those isn't reliable across Laravel
versions, so this repo ships only the code that's actually specific to
Tasklane — the same approach you'd use when adding a feature to any
real Laravel app.
