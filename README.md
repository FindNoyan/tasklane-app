# Tasklane — Kanban Task Manager (Laravel)

A Kanban-style task board — To do / In progress / Done — with multiple
boards, drag and drop, search and filters, priority markers, and due
dates. Built with Laravel and Blade, styled with Tailwind CSS.

This folder contains the **custom application code only** (models,
migration, controller, routes, views). It's meant to be dropped into a
fresh Laravel install rather than run standalone, so you always get
the correct framework files for whatever Laravel version you install.

## Features

- **Multiple boards** — create, switch between, rename, and delete
  boards. Each board has its own tasks, and a task can be moved to
  another board from its edit page.
- **Full task CRUD** — create, edit, move, and delete tasks. Tasks
  have a title, description, priority (low / medium / high), status,
  and an optional due date.
- **Drag and drop** — drag a card onto another column to move it, or
  drag it within its own column to reorder it. The order is saved,
  so the board looks the same after a refresh. The Move button and
  the forms still work without JavaScript.
- **Search and filter** — search tasks by title, filter by priority,
  or show overdue tasks only. Clearing the filters returns to the
  full board.
- **Due dates** — tasks due today are labelled "due today", and
  overdue tasks are flagged. Within a column, tasks are sorted by
  due date first (no due date last), then by priority.
- **Delete confirmation** — deleting a task or a board asks first,
  so a misclick can't wipe anything.
- **Validated input** — task and board forms are validated through a
  Form Request (`TaskRequest`), so the controller stays thin and the
  same rules apply to creating and editing.

## Screenshots

No screenshots yet. When board screenshots are added, they will be
linked here.

## Project structure

```
app/
  Models/
    Board.php            # Board (name) + its tasks
    Task.php             # Task + overdue / due-today helpers, ordering + filter scopes
  Http/
    Controllers/
      BoardController.php   # Create / rename / delete boards
      TaskController.php    # Board page, create / edit / move / delete / reorder tasks
    Requests/
      TaskRequest.php       # Shared validation rules for creating and editing tasks
database/
  migrations/            # Three Tasklane migrations (boards, tasks, task position)
  seeders/
    DatabaseSeeder.php   # Seed data
routes/
  web.php                # Board + task routes
resources/
  views/
    layouts/app.blade.php
    boards/edit.blade.php    # Rename a board
    tasks/
      index.blade.php        # The board: switcher, search form, columns, cards
      edit.blade.php         # Edit a task (incl. move it to another board)
  js/app.js              # Delete confirmations + drag and drop
  css/app.css            # Board + drag styles
tests/
  Feature/               # 9 feature test files (boards, CRUD, ordering, search, drag and drop, reorder)
  Unit/
    TaskOverdueTest.php  # Due-today / overdue rules
tailwind.config.js
postcss.config.js
```

## Setup

1. **Create a fresh Laravel project** (this generates all the standard
   framework files):
   ```bash
   composer create-project laravel/laravel tasklane
   ```

2. **Copy these folders into the new `tasklane` project**, replacing
   files where asked:
   - `app/Models/Board.php`, `app/Models/Task.php`
   - `app/Http/Controllers/BoardController.php`,
     `app/Http/Controllers/TaskController.php`
   - `app/Http/Requests/TaskRequest.php`
   - `database/migrations/*` (the three Tasklane migration files)
   - `database/seeders/DatabaseSeeder.php`
   - `routes/web.php`
   - `resources/views/layouts/app.blade.php`
   - `resources/views/boards/edit.blade.php`
   - `resources/views/tasks/index.blade.php`
   - `resources/views/tasks/edit.blade.php`
   - `resources/js/app.js`
   - `resources/css/app.css`
   - `tests/` (feature + unit tests — run them with
     `php artisan test`)
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

## Running the tests

The suite is 9 feature test files plus 1 unit test file, covering
boards (create / switch / rename / delete / move a task), the task
CRUD flows, column ordering, search and filters, validation, delete
confirmation, drag-and-drop hooks, and reordering within a column.

Run the whole suite:

```bash
php artisan test
```

Run one test file, for example the reorder tests:

```bash
php artisan test --filter=TaskReorderTest
```

## Why it's structured this way

Laravel projects are normally created with the official installer,
which generates dozens of framework boilerplate files (kernel,
middleware, config). Hand-copying those isn't reliable across Laravel
versions, so this repo ships only the code that's actually specific to
Tasklane — the same approach you'd use when adding a feature to any
real Laravel app.
