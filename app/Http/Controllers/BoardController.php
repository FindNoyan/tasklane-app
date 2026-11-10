<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BoardController extends Controller
{
    /**
     * Store a newly created board and open it.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Unique so the board switcher never lists two boards
            // with the same name; the user cannot tell them apart.
            'name' => ['required', 'string', 'max:255', 'unique:boards,name'],
        ]);

        $board = Board::create($validated);

        return redirect()->route('tasks.index', ['board' => $board->id])
            ->with('status', 'Board created.');
    }

    /**
     * Show the form for renaming a board.
     */
    public function edit(Board $board): View
    {
        return view('boards.edit', ['board' => $board]);
    }

    /**
     * Rename a board.
     */
    public function update(Request $request, Board $board): RedirectResponse
    {
        $validated = $request->validate([
            // Unique, ignoring this board: renaming a board to its
            // own current name is allowed, renaming it to another
            // board's name is not.
            'name' => ['required', 'string', 'max:255', Rule::unique('boards')->ignore($board->id)],
        ]);

        $board->update($validated);

        return redirect()->route('tasks.index', ['board' => $board->id])
            ->with('status', 'Board renamed.');
    }

    /**
     * Delete a board and all its tasks.
     */
    public function destroy(Board $board): RedirectResponse
    {
        // The tasks table's board_id foreign key cascades on
        // delete, so the board's tasks go with it — nothing is
        // orphaned. The board page falls back to the first
        // remaining board, or recreates the default board when
        // none is left.
        $board->delete();

        return redirect()->route('tasks.index')
            ->with('status', 'Board deleted.');
    }
}
