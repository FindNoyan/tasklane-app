<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDragDropTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_renders_draggable_cards_with_task_ids(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $task = Task::create([
            'board_id' => $board->id,
            'title' => 'Draggable task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        // The card carries the hooks the drag-and-drop script needs:
        // draggable plus the task id it hands to the drop handler.
        $this->get(route('tasks.index', ['board' => $board->id]))->assertOk()
            ->assertSee('draggable="true"', false)
            ->assertSee('data-task-id="' . $task->id . '"', false)
            ->assertSee('Tip: drag a task onto another column to move it, or drag it within a column to reorder it.');
    }

    public function test_every_column_is_a_drop_target_even_when_empty(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        // No tasks at all, so all three columns render their empty
        // state — each one must still accept a dropped card.
        $this->get(route('tasks.index', ['board' => $board->id]))->assertOk()
            ->assertSee('data-drop-status="todo"', false)
            ->assertSee('data-drop-status="in_progress"', false)
            ->assertSee('data-drop-status="done"', false);
    }

    public function test_move_form_fallback_is_still_present_on_each_card(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        Task::create([
            'board_id' => $board->id,
            'title' => 'Movable task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        // Without JavaScript the drag never runs, so the card's own
        // status select + Move button must still be there — and the
        // drag script finds that same form through data-move-form.
        $this->get(route('tasks.index', ['board' => $board->id]))->assertOk()
            ->assertSee('data-move-form', false)
            ->assertSee('name="status"', false)
            ->assertSee('Move');
    }

    public function test_board_renders_the_hidden_reorder_form(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        // The drag script posts a same-column drop through this
        // form, so it must be on the page with the board id and an
        // empty status for the script to fill in.
        $this->get(route('tasks.index', ['board' => $board->id]))->assertOk()
            ->assertSee('data-reorder-form', false)
            ->assertSee('name="board_id"', false)
            ->assertSee('name="status"', false);
    }
}
