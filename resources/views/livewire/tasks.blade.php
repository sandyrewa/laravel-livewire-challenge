<?php

use App\Models\Task;
use Livewire\Volt\Component;

new class extends Component {
    public string $title = '';

    /**
     * Create a task from the title input, then clear the input.
     */
    public function addTask(): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
        ]);

        Task::create([
            'title' => $validated['title'],
            'completed' => false,
        ]);

        $this->title = '';
    }

    /**
     * Flip a task between complete and incomplete.
     */
    public function toggle(int $id): void
    {
        $task = Task::findOrFail($id);

        $task->update([
            'completed' => ! $task->completed,
        ]);
    }

    /**
     * Data passed to the view on every render.
     */
    public function with(): array
    {
        return [
            'tasks' => Task::latest()->get(),
        ];
    }
}; ?>

<div class="mx-auto max-w-md p-6">
    <h1 class="mb-4 text-xl font-semibold">Tasks</h1>

    <form wire:submit="addTask" class="mb-6 flex gap-2">
        <input
            type="text"
            wire:model="title"
            placeholder="What needs doing?"
            class="flex-1 rounded border border-gray-300 px-3 py-2"
        />
        <button
            type="submit"
            class="rounded bg-gray-900 px-4 py-2 text-white"
        >
            Add
        </button>
    </form>

    @error('title')
        <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <ul class="space-y-2">
        @forelse ($tasks as $task)
            <li class="flex items-center gap-3 rounded border border-gray-200 px-3 py-2">
                <input
                    type="checkbox"
                    wire:click="toggle({{ $task->id }})"
                    @checked($task->completed)
                />
                <span class="{{ $task->completed ? 'text-gray-400 line-through' : '' }}">
                    {{ $task->title }}
                </span>
            </li>
        @empty
            <li class="text-sm text-gray-500">No tasks yet.</li>
        @endforelse
    </ul>
</div>
