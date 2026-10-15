@extends('layouts.app')

@section('title', 'Edit task')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h2 class="font-display text-lg font-semibold text-ink">Edit task</h2>

        <form method="POST" action="{{ route('tasks.update', $task) }}" class="mt-4 grid gap-4 rounded-lg border border-line bg-surface p-5">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="mb-1 block text-sm font-medium text-ink">Title</label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title', $task->title) }}"
                    required
                    class="w-full rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none"
                >
                @error('title')
                    <p class="mt-1 text-sm text-ochre">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-ink">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="w-full rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none"
                >{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-ochre">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-ink">Status</label>
                    <select id="status" name="status" class="w-full rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none">
                        <option value="todo" @selected(old('status', $task->status) === 'todo')>To do</option>
                        <option value="in_progress" @selected(old('status', $task->status) === 'in_progress')>In progress</option>
                        <option value="done" @selected(old('status', $task->status) === 'done')>Done</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-ochre">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="priority" class="mb-1 block text-sm font-medium text-ink">Priority</label>
                    <select id="priority" name="priority" class="w-full rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none">
                        <option value="low" @selected(old('priority', $task->priority) === 'low')>Low</option>
                        <option value="medium" @selected(old('priority', $task->priority) === 'medium')>Medium</option>
                        <option value="high" @selected(old('priority', $task->priority) === 'high')>High</option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-ochre">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="mb-1 block text-sm font-medium text-ink">Due date</label>
                    <input
                        id="due_date"
                        type="date"
                        name="due_date"
                        value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                        class="w-full rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none"
                    >
                    @error('due_date')
                        <p class="mt-1 text-sm text-ochre">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    class="rounded-md bg-ink px-4 py-2 text-sm font-medium text-paper transition-opacity hover:opacity-90"
                >
                    Save changes
                </button>
                <a href="{{ route('tasks.index') }}" class="text-sm text-muted hover:underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
