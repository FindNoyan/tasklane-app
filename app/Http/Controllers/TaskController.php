<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Board;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Show the board with tasks grouped by status.
     */
    public function index(Request $request): View
    {
        // ?board=<id> switches boards. Only a numeric id is looked
        // up: anything else (a missing value, an unknown id, or an
        // array like ?board[]=1, which would make find() return a
        // Collection instead of a Board) falls back to the first
        // board, never an error — the same way an unknown filter
        // value is ignored below instead of failing the request.
        $boardId = $request->query('board');
        $board = is_numeric($boardId) ? Board::find($boardId) : null;
        $board ??= Board::first() ?? Board::firstOrCreate(['name' => 'My Board']);

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

        // A new task joins the end of the To do column, so it never
        // jumps ahead of a column the user ordered by hand. The
        // position is set directly, not mass-assigned: it is an
        // internal ordering value, never part of a form's input. The
        // end position is read before creating, so the new row
        // itself is not counted as the column's last task.
        $position = $this->endPosition($validated['board_id'], 'todo');
        $task = Task::create($validated + ['status' => 'todo']);
        $task->position = $position;
        $task->save();

        return redirect()->route('tasks.index', ['board' => $validated['board_id']])
            ->with('status', 'Task added.');
    }

    /**
     * Show the form for editing a task.
     */
    public function edit(Task $task): View
    {
        return view('tasks.edit', [
            'task' => $task,
            // The edit form has a board picker, so the task can move
            // to another board when it is saved.
            'boards' => Board::orderBy('name')->get(),
        ]);
    }

    /**
     * Update a task's editable fields.
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $oldBoardId = $task->board_id;
        $oldStatus = $task->status;

        $task->update($request->validated());

        // The edit form can move the task to another board or
        // column, so — same as a column move — it joins the end of
        // the column it lands in instead of arriving with a stale
        // position that would scramble a hand-ordered column.
        if ($task->board_id !== $oldBoardId || $task->status !== $oldStatus) {
            $task->position = $this->endPosition($task->board_id, $task->status);
            $task->save();
        }

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

        // Moving to another column appends the task at the end of
        // that column, so a manually ordered column is not scrambled
        // by a task arriving with a stale position. Submitting the
        // Move form without changing the status leaves the position
        // alone, exactly as before.
        if ($validated['status'] !== $task->status) {
            $task->position = $this->endPosition($task->board_id, $validated['status']);
        }

        $task->update($validated);

        return redirect()->route('tasks.index', ['board' => $task->board_id])
            ->with('status', 'Task updated.');
    }

    /**
     * Save a column's manual order after a drag-and-drop reorder.
     * The posted ids must be every task in that board + status
     * column, in their new order: a filtered board only shows some
     * tasks, so a partial order is rejected instead of scrambling
     * the tasks the page did not show.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'board_id' => ['required', 'exists:boards,id'],
            'status' => ['required', 'in:todo,in_progress,done'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tasks,id'],
        ]);

        $ids = array_values(array_unique($validated['ids']));

        $matching = Task::whereIn('id', $ids)
            ->where('board_id', $validated['board_id'])
            ->where('status', $validated['status'])
            ->count();

        $columnCount = Task::where('board_id', $validated['board_id'])
            ->where('status', $validated['status'])
            ->count();

        if ($matching !== count($ids) || $matching !== $columnCount) {
            return redirect()->route('tasks.index', ['board' => $validated['board_id']])
                ->withErrors(['ids' => 'The task order must list every task in the column.']);
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $position => $id) {
                Task::where('id', $id)->update(['position' => $position]);
            }
        });

        return redirect()->route('tasks.index', ['board' => $validated['board_id']])
            ->with('status', 'Order saved.');
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

    /**
     * The position a task takes when it enters a column: one past
     * the column's current last task, or 0 for an empty column.
     */
    private function endPosition(int $boardId, string $status): int
    {
        $max = Task::where('board_id', $boardId)->where('status', $status)->max('position');

        return ($max ?? -1) + 1;
    }
}
