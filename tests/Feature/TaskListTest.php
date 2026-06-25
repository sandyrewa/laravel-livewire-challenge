<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('creates the tasks table with the expected columns', function () {
    expect(Schema::hasTable('tasks'))->toBeTrue();
    expect(Schema::hasColumns('tasks', [
        'id', 'title', 'completed', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

it('renders the tasks Volt component', function () {
    Volt::test('tasks')->assertOk();
});

it('lists existing tasks', function () {
    Task::create(['title' => 'Existing task', 'completed' => false]);

    Volt::test('tasks')->assertSee('Existing task');
});

it('creates a task with a valid title', function () {
    Volt::test('tasks')
        ->set('title', 'Write the migration')
        ->call('addTask')
        ->assertHasNoErrors();

    expect(Task::where('title', 'Write the migration')->exists())->toBeTrue();
});

it('resets the title input after creating a task', function () {
    Volt::test('tasks')
        ->set('title', 'Temporary')
        ->call('addTask')
        ->assertSet('title', '');
});

it('rejects an empty title and saves nothing', function () {
    Volt::test('tasks')
        ->set('title', '')
        ->call('addTask')
        ->assertHasErrors(['title' => 'required']);

    expect(Task::count())->toBe(0);
});

it('toggles a task between complete and incomplete', function () {
    $task = Task::create(['title' => 'Toggle me', 'completed' => false]);

    Volt::test('tasks')->call('toggle', $task->id);
    expect($task->fresh()->completed)->toBeTrue();

    Volt::test('tasks')->call('toggle', $task->id);
    expect($task->fresh()->completed)->toBeFalse();
});
