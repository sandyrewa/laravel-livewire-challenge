# AI Conversation — Build Challenge

Tool used: **Claude Code**

This is the conversation I had with Claude Code while building the Volt task-list
component for this challenge. My approach was to let it read the contract (the
pre-written Pest tests and the README) first, drive the implementation, and then
verify everything against the test suite before pushing.

---

## 1. Setting context

**Me:** I've cloned the challenge repo. Before writing any code, read the README
and the pre-written Pest suite in `tests/Feature/TaskListTest.php` and tell me the
exact contract I have to build to. I don't want to guess at column names or method
signatures — the tests are the source of truth.

**Claude:** Read both. The contract is precise:

- **Migration** — a `tasks` table with `id`, `title` (string), `completed`
  (boolean, default `false`), `created_at`, `updated_at`.
- **Model** — `App\Models\Task` with `title` and `completed` mass-assignable.
- **Volt component** — named `tasks` at
  `resources/views/livewire/tasks.blade.php`, mounted at `/tasks`, exposing:
  - public property `$title` (string),
  - `addTask()` validating `title` as `required|string|max:255`, creating the
    task, and resetting `$title` to `''`,
  - `toggle($id)` flipping that task's `completed` value.

The tests call the component via `Volt::test('tasks')`, use `RefreshDatabase`, and
assert on errors (`required`), on `$title` resetting to `''`, and on the toggle
flipping the boolean both ways. The README says not to edit the test file or change
package versions.

---

## 2. Database decision

**Me:** The template's `.env.example` defaults to SQLite and the GitHub Actions
workflow spins up its own MySQL 8 `testing` database. For the local dev loop, run
against SQLite so I don't need a running MySQL server — CI will prove it on MySQL.
Agreed?

**Claude:** Agreed — that's the pragmatic choice. The schema (`string` + `boolean`)
is portable, the migration is database-agnostic, and `tests.yml` already runs the
suite against MySQL 8 on every push, so the "works on MySQL" requirement is proven
where it's actually checked. Local SQLite just makes the inner loop fast. Setup:

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
```

---

## 3. The migration

**Me:** Write the `tasks` migration to match the contract exactly.

**Claude:** Created `database/migrations/2025_06_25_000000_create_tasks_table.php`:

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->boolean('completed')->default(false);
    $table->timestamps();
});
```

`default(false)` matches the contract so a task is incomplete unless told
otherwise, and `timestamps()` gives the `created_at` / `updated_at` columns the
schema test checks for.

---

## 4. The model

**Me:** Now the `Task` model. Make sure `completed` behaves as a real boolean —
I don't want a `"0"`/`"1"` string leaking through and breaking the toggle test.

**Claude:** Good call. Created `app/Models/Task.php` with both fields fillable and a
boolean cast:

```php
protected $fillable = ['title', 'completed'];

protected function casts(): array
{
    return ['completed' => 'boolean'];
}
```

The cast is what makes `! $task->completed` flip cleanly between `true` and
`false`, which is exactly what the toggle test asserts (`toBeTrue()` then
`toBeFalse()`).

---

## 5. The Volt component

**Me:** Build the Volt single-file component. Use the class-based API — I find it
more readable than the functional helpers for something with multiple methods.
Keep the markup simple; the README says they're grading code, not design.

**Claude:** Created `resources/views/livewire/tasks.blade.php`. Key decisions:

- `public string $title = '';` — typed, starts empty.
- `addTask()` uses `$this->validate([...])` so a failed validation populates the
  Livewire error bag (the test asserts `assertHasErrors(['title' => 'required'])`),
  then creates the task and sets `$this->title = ''` to satisfy the reset test.
- `toggle(int $id)` loads the task with `findOrFail` and `update(['completed' =>
  ! $task->completed])`.
- A `with()` method returns `Task::latest()->get()` so the newest task shows first
  and the list re-renders reactively after every action.

The Blade has a `wire:submit="addTask"` form, an `@error('title')` block for the
validation message, and a `@forelse` list where each row has a
`wire:click="toggle(...)"` checkbox plus a line-through style when completed.

---

## 6. The route

**Me:** Mount it at `/tasks`.

**Claude:** Added to `routes/web.php`:

```php
use Livewire\Volt\Volt;

Volt::route('/tasks', 'tasks');
```

`Volt::route()` mounts the single-file component as a full-page route, which is
what the README asks for.

---

## 7. Verification

**Me:** Migrate and run the full suite. I'm not calling this done until it's green.

**Claude:** Ran `php artisan migrate --force` then `php artisan test`:

```
PASS  Tests\Feature\TaskListTest
✓ it creates the tasks table with the expected columns
✓ it renders the tasks Volt component
✓ it lists existing tasks
✓ it creates a task with a valid title
✓ it resets the title input after creating a task
✓ it rejects an empty title and saves nothing
✓ it toggles a task between complete and incomplete

Tests: 9 passed (15 assertions)
```

All 7 challenge tests pass, plus the 2 default example tests. Contract met.

---

## 8. Browser check + layout fix

**Me:** Tests are green, but open `/tasks` in a real browser too — I want to see
it actually work, not just pass assertions.

**Claude:** Started `php artisan serve` and loaded `/tasks`. It threw
`Livewire page component layout view not found: [components.layouts.app]`. That's
expected: `Volt::route()` renders the component as a full page, which needs a
layout view, but the Pest suite uses `Volt::test()` to render the component in
isolation — so the tests pass without it. Added a minimal
`resources/views/components/layouts/app.blade.php` (with `@livewireStyles` /
`@livewireScripts` and Tailwind via CDN for quick styling). Reloaded and drove it:
added "Ship the Livewire challenge" (it appeared at the top and the input cleared),
then toggled it complete (checkbox checked, title struck through). List, create
and toggle all confirmed working in the browser. Re-ran the suite — still green.

---

## 9. How I worked with the AI

I treated the pre-written tests as the spec and had Claude build to them, but I
made the engineering calls myself: SQLite for the local loop (CI handles MySQL),
the boolean cast on `completed` so the toggle is reliable, the class-based Volt API
for readability, and `latest()` ordering so new tasks surface first. I read every
file before running anything and only considered it finished once the suite was
green — same review-and-verify discipline I use on production work.
