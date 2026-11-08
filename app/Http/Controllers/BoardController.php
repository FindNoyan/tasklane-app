<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
