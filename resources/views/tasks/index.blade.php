@extends('layouts.app')

@section('title', $board->name)

@section('content')
    <div class="mx-auto max-w-5xl">

        <nav aria-label="Boards" class="mb-6 flex flex-wrap items-center gap-2">
            @foreach ($boards as $listedBoard)
                <a
                    href="{{ route('tasks.index', ['board' => $listedBoard->id]) }}"
                    @if ($listedBoard->id === $board->id) aria-current="page" @endif
                    class="rounded-md border px-3 py-1.5 text-sm {{ $listedBoard->id === $board->id ? 'border-ink bg-ink font-medium text-paper' : 'border-line bg-surface text-ink hover:border-teal' }}"
                >
                    {{ $listedBoard->name }}
                </a>
            @endforeach
        </nav>

        <div class="mb-6 flex items-center gap-4 text-sm">
            <span class="text-muted">This board:</span>
            <a href="{{ route('boards.edit', $board) }}" class="text-teal hover:underline">
                Rename
            </a>
            <form method="POST" action="{{ route('boards.destroy', $board) }}" data-confirm-board-delete class="inline">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="text-muted transition-colors hover:text-ochre"
                >
                    Delete board
                </button>
            </form>
        </div>

        <section class="mb-10 rounded-lg border border-line bg-surface p-5">
            <h2 class="font-display text-lg font-semibold text-ink">New board</h2>
            <form method="POST" action="{{ route('boards.store') }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <label class="flex flex-col gap-1 text-xs text-muted">
                    Board name
                    <input
                        type="text"
                        name="name"
                        placeholder="Board name"
                        value="{{ old('name') }}"
                        required
                        class="rounded-md border border-line px-3 py-2 text-sm text-ink focus:border-teal focus:outline-none"
                    >
                </label>
                <button
                    type="submit"
                    class="rounded-md bg-ink px-4 py-2 text-sm font-medium text-paper transition-opacity hover:opacity-90"
                >
                    Create board
                </button>
            </form>
            @error('name')
                <p class="mt-2 text-sm text-ochre">{{ $message }}</p>
            @enderror
        </section>

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

        <section class="mb-10 rounded-lg border border-line bg-surface p-5">
            <h2 class="font-display text-lg font-semibold text-ink">Find a task</h2>
            <form method="GET" action="{{ route('tasks.index') }}" class="mt-4 flex flex-wrap items-end gap-3">
                <input type="hidden" name="board" value="{{ $board->id }}">
                <label class="flex flex-col gap-1 text-xs text-muted">
                    Search by title
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Search titles"
                        class="rounded-md border border-line px-3 py-2 text-sm text-ink focus:border-teal focus:outline-none"
                    >
                </label>
                <label class="flex flex-col gap-1 text-xs text-muted">
                    Priority
                    <select name="priority" class="rounded-md border border-line px-3 py-2 text-sm text-ink focus:border-teal focus:outline-none">
                        <option value="" @selected($filters['priority'] === null || $filters['priority'] === '')>All priorities</option>
                        <option value="low" @selected($filters['priority'] === 'low')>Low</option>
                        <option value="medium" @selected($filters['priority'] === 'medium')>Medium</option>
                        <option value="high" @selected($filters['priority'] === 'high')>High</option>
                    </select>
                </label>
                <label class="flex items-center gap-2 pb-2 text-sm text-ink">
                    <input type="checkbox" name="overdue" value="1" @checked($filters['overdue'])>
                    Overdue only
                </label>
                <button
                    type="submit"
                    class="rounded-md bg-ink px-4 py-2 text-sm font-medium text-paper transition-opacity hover:opacity-90"
                >
                    Search
                </button>
                @if ($filtersActive)
                    <a href="{{ route('tasks.index', ['board' => $board->id]) }}" class="pb-2 text-sm text-teal hover:underline">
                        Clear filters
                    </a>
                @endif
            </form>
        </section>

        @if ($filtersActive && $columns['todo']->isEmpty() && $columns['in_progress']->isEmpty() && $columns['done']->isEmpty())
            <p class="mb-6 text-sm text-muted">
                No tasks match your filters.
                <a href="{{ route('tasks.index', ['board' => $board->id]) }}" class="text-teal hover:underline">Clear filters</a>
            </p>
        @endif

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
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" data-confirm-delete>
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
                                        @if ($task->isDueToday()) · due today @endif
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
