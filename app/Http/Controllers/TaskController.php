<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
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
        $board = Board::firstOrCreate(['name' => 'My Board']);

        // Sort in the query, not in the view: Task::ordered() puts the
        // earliest due date first (no due date last), then high
        // priority first. Collection::where() below preserves that
        // order, so each column reads in the same order.
        $tasks = $board->tasks()->ordered()->get();

        $columns = [
            'todo' => $tasks->where('status', 'todo'),
            'in_progress' => $tasks->where('status', 'in_progress'),
            'done' => $tasks->where('status', 'done'),
        ];

        return view('tasks.index', [
            'board' => $board,
            'columns' => $columns,
        ]);
    }

    /**
     * Store a newly created task on the board.
     */
    public function store(TaskRequest $request): RedirectResponse
    {
        Task::create($request->validated() + ['status' => 'todo']);

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
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

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
