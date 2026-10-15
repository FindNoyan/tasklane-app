<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Show the board with tasks grouped by status.
     */
    public function index(): View
    {
        $board = Board::with('tasks')->firstOrCreate(['name' => 'My Board']);

        $columns = [
            'todo' => $board->tasks->where('status', 'todo'),
            'in_progress' => $board->tasks->where('status', 'in_progress'),
            'done' => $board->tasks->where('status', 'done'),
        ];

        return view('tasks.index', [
            'board' => $board,
            'columns' => $columns,
        ]);
    }

    /**
     * Store a newly created task on the board.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'board_id' => ['required', 'exists:boards,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);

        Task::create($validated + ['status' => 'todo']);

        return redirect()->route('tasks.index')->with('status', 'Task added.');
    }

    /**
     * Show the form for editing a task.
     */
    public function edit(Task $task): View
    {
        return view('tasks.edit', ['task' => $task]);
    }

    /**
     * Update a task's editable fields.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,in_progress,done'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    /**
     * Move a task to a new status column.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:todo,in_progress,done'],
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    /**
     * Remove a task from the board.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Task removed.');
    }
}
