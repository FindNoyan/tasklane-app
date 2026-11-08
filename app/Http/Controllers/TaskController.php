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
    public function index(Request $request): View
    {
        // ?board=<id> switches boards. An unknown, missing, or
        // non-numeric id falls back to the first board, never an
        // error — the same way an unknown filter value is ignored
        // below instead of failing the request.
        $board = Board::find($request->query('board'))
            ?? Board::first()
            ?? Board::firstOrCreate(['name' => 'My Board']);

        // Search + filter come from the board's GET form and are
        // whitelisted here, so an unknown value never reaches the
        // query as an error: Task::filtered() ignores it.
        $filters = [
            'search' => is_string($request->query('search')) ? trim($request->query('search')) : '',
            'priority' => is_string($request->query('priority')) ? $request->query('priority') : null,
            'overdue' => $request->query('overdue') === '1',
        ];

        $filtersActive = $filters['search'] !== ''
            || in_array($filters['priority'], ['low', 'medium', 'high'], true)
            || $filters['overdue'];

        // Filter in the query, then sort in the query, not in the
        // view: Task::filtered() narrows the rows and Task::ordered()
        // puts the earliest due date first (no due date last), then
        // high priority first. Collection::where() below preserves
        // that order, so each column reads in the same order.
        $tasks = $board->tasks()->filtered($filters)->ordered()->get();

        $columns = [
            'todo' => $tasks->where('status', 'todo'),
            'in_progress' => $tasks->where('status', 'in_progress'),
            'done' => $tasks->where('status', 'done'),
        ];

        return view('tasks.index', [
            'board' => $board,
            'boards' => Board::orderBy('name')->get(),
            'columns' => $columns,
            'filters' => $filters,
            'filtersActive' => $filtersActive,
        ]);
    }

    /**
     * Store a newly created task on the board.
     */
    public function store(TaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Task::create($validated + ['status' => 'todo']);

        return redirect()->route('tasks.index', ['board' => $validated['board_id']])
            ->with('status', 'Task added.');
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

        return redirect()->route('tasks.index', ['board' => $task->board_id])
            ->with('status', 'Task updated.');
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

        return redirect()->route('tasks.index', ['board' => $task->board_id])
            ->with('status', 'Task updated.');
    }

    /**
     * Remove a task from the board.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index', ['board' => $task->board_id])
            ->with('status', 'Task removed.');
    }
}
