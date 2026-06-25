# Build Challenge — PHP / Laravel Developer

Thanks for applying. This is a short build challenge. A senior Laravel
developer using AI tooling should finish it in **about 45 minutes**.

You are **encouraged to use Claude Code / Cursor** — that is part of what we are
evaluating. The one rule: **you must commit your AI conversation** (see step 4).

## Stack (required)
- PHP 8.3+
- Laravel 11
- Livewire 3
- Volt (single-file components)
- Pest (tests are already written for you)
- MySQL

These are already installed in this template. Do not change versions.

## The task
Build a single **Volt single-file component** that manages a task list, backed
by a MySQL table through Eloquent.

It must:
1. **List** all tasks.
2. **Create** a task from a title input. An empty title must be rejected with a
   validation error and must not be saved.
3. **Toggle** a task between complete and incomplete.

Keep it simple and clean. We are reading your code, not grading visual design.

## The contract (build to this exactly — the tests depend on it)
A migration creates a **`tasks`** table with these columns: `id`, `title`
(string), `completed` (boolean, default `false`), `created_at`, `updated_at`.

A **`Task`** Eloquent model (`App\Models\Task`) with `title` and `completed`
mass-assignable.

A Volt component named **`tasks`** (file:
`resources/views/livewire/tasks.blade.php`) mounted at the route **`/tasks`**,
with:
- a public property **`$title`** (string),
- a method **`addTask()`** that validates `title` as `required|string|max:255`,
  creates the task, and resets `$title`,
- a method **`toggle($id)`** that flips that task's `completed` value.

The provided Pest suite (`tests/Feature/TaskListTest.php`) checks all of the
above. Do not edit the test file.

## Run the tests locally
```bash
composer install
cp .env.example .env
php artisan key:generate
# point .env at your local MySQL, then:
php artisan migrate
php artisan test
```
Green across the board = you have met the spec.

## Submit
1. Click **"Use this template"** at the top of this repo to make your own copy.
   Keep it **public**.
2. Build the task. Commit as you go.
3. **Commit your AI conversation** as a file named **`AI-CONVERSATION.md`** at
   the repo root — paste the full conversation you had with Claude / Cursor
   while building this. This is required; an entry without it is incomplete.
4. **Push.** GitHub Actions runs the test suite automatically — you'll see a
   green check or red X on your latest commit.
5. Go back to the application form and **paste your repository URL** into the
   "Build challenge" field.

That's it. We review your code, your test result, and how you worked with AI,
all from your repo. Good luck.
