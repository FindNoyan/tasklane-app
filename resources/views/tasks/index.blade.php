@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl">

        <section class="mb-10 rounded-lg border border-line bg-surface p-5">
            <h2 class="font-display text-lg font-semibold text-ink">Add a task</h2>
            <form method="POST" action="{{ route('tasks.store') }}" class="mt-4">
                @csrf
                <input type="hidden" name="board_id" value="{{ $board->id }}">
                <div class="grid gap-3 sm:grid-cols-[2fr_1fr_1fr_auto]">
                    <input
                        type="text"
                        name="title"
                        placeholder="Task title"
                        value="{{ old('title') }}"
                        required
                        class="rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none"
                    >
                    <select name="priority" class="rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none">
                        <option value="low" @selected(old('priority', 'medium') === 'low')>Low</option>
                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                        <option value="high" @selected(old('priority', 'medium') === 'high')>High</option>
                    </select>
                    <input
                        type="date"
                        name="due_date"
                        value="{{ old('due_date') }}"
                        class="rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none"
                    >
                    <button
                        type="submit"
                        class="rounded-md bg-ink px-4 py-2 text-sm font-medium text-paper transition-opacity hover:opacity-90"
                    >
                        Add
                    </button>
                </div>
                <textarea
                    name="description"
                    placeholder="Description (optional)"
                    rows="2"
                    class="mt-3 w-full rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none"
                >{{ old('description') }}</textarea>
            </form>
            @foreach (['board_id', 'title', 'description', 'priority', 'due_date'] as $field)
                @error($field)
                    <p class="mt-2 text-sm text-ochre">{{ $message }}</p>
                @enderror
            @endforeach
        </section>

        <div class="grid gap-6 sm:grid-cols-3">
            @foreach ([
                'todo' => 'To do',
                'in_progress' => 'In progress',
                'done' => 'Done',
            ] as $status => $label)
                <section>
                    <h3 class="mb-3 font-display text-base font-semibold text-ink">
                        {{ $label }}
                        <span class="ml-1 font-body text-sm font-normal text-muted">
                            ({{ $columns[$status]->count() }})
                        </span>
                    </h3>

                    <div class="space-y-3">
                        @forelse ($columns[$status] as $task)
                            <article class="priority-bar--{{ $task->priority }} rounded-md border border-line border-l-4 bg-surface p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-sm font-medium text-ink">{{ $task->title }}</h4>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <a
                                            href="{{ route('tasks.edit', $task) }}"
                                            class="text-xs text-teal hover:underline"
                                        >
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="text-xs text-muted transition-colors hover:text-ochre"
                                                aria-label="Remove task"
                                            >
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if ($task->description)
                                    <p class="mt-1 text-sm text-muted">{{ $task->description }}</p>
                                @endif

                                @if ($task->due_date)
                                    <p class="mt-1 text-xs {{ $task->isOverdue() ? 'text-ochre' : 'text-muted' }}">
                                        Due {{ $task->due_date->format('M j') }}
                                        @if ($task->isOverdue()) · overdue @endif
                                    </p>
                                @endif

                                <form method="POST" action="{{ route('tasks.updateStatus', $task) }}" class="mt-3 flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-md border border-line px-2 py-1 text-xs focus:border-teal focus:outline-none">
                                        <option value="todo" @selected($task->status === 'todo')>To do</option>
                                        <option value="in_progress" @selected($task->status === 'in_progress')>In progress</option>
                                        <option value="done" @selected($task->status === 'done')>Done</option>
                                    </select>
                                    <button type="submit" class="text-xs text-teal hover:underline">
                                        Move
                                    </button>
                                </form>
                            </article>
                        @empty
                            <p class="text-sm text-muted">Nothing here yet.</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection
