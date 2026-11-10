@extends('layouts.app')

@section('title', 'Rename board')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h2 class="font-display text-lg font-semibold text-ink">Rename board</h2>

        <form method="POST" action="{{ route('boards.update', $board) }}" class="mt-4 grid gap-4 rounded-lg border border-line bg-surface p-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-ink">Board name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $board->name) }}"
                    required
                    class="w-full rounded-md border border-line px-3 py-2 text-sm focus:border-teal focus:outline-none"
                >
                @error('name')
                    <p class="mt-1 text-sm text-ochre">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    class="rounded-md bg-ink px-4 py-2 text-sm font-medium text-paper transition-opacity hover:opacity-90"
                >
                    Save changes
                </button>
                <a href="{{ route('tasks.index', ['board' => $board->id]) }}" class="text-sm text-muted hover:underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
