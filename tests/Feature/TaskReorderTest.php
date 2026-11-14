<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reorder_persists_positions_and_board_renders_the_new_order(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $first = Task::create([
            'board_id' => $board->id,
            'title' => 'First task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        $second = Task::create([
            'board_id' => $board->id,
            'title' => 'Second task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        $third = Task::create([
            'board_id' => $board->id,
            'title' => 'Third task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $this->post(route('tasks.reorder'), [
            'board_id' => $board->id,
            'status' => 'todo',
            'ids' => [$third->id, $first->id, $second->id],
        ])->assertRedirect(route('tasks.index', ['board' => $board->id]))
            ->assertSessionHas('status', 'Order saved.');

        // Positions follow the posted order, 0..n.
        $this->assertDatabaseHas('tasks', ['id' => $third->id, 'position' => 0]);
        $this->assertDatabaseHas('tasks', ['id' => $first->id, 'position' => 1]);
        $this->assertDatabaseHas('tasks', ['id' => $second->id, 'position' => 2]);

        // And the board renders the tasks in that order.
        $this->get(route('tasks.index', ['board' => $board->id]))->assertOk()
            ->assertSeeInOrder(['Third task', 'First task', 'Second task']);
    }

    public function test_reorder_rejects_a_task_from_another_board(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);
        $otherBoard = Board::create(['name' => 'Other Board']);

        $task = Task::create([
            'board_id' => $board->id,
            'title' => 'My task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        $foreign = Task::create([
            'board_id' => $otherBoard->id,
            'title' => 'Foreign task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $this->post(route('tasks.reorder'), [
            'board_id' => $board->id,
            'status' => 'todo',
            'ids' => [$foreign->id, $task->id],
        ])->assertSessionHasErrors('ids');

        // Nothing was reordered.
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'position' => 0]);
        $this->assertDatabaseHas('tasks', ['id' => $foreign->id, 'position' => 0]);
    }

    public function test_reorder_rejects_a_partial_column_order(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $first = Task::create([
            'board_id' => $board->id,
            'title' => 'First task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        $second = Task::create([
            'board_id' => $board->id,
            'title' => 'Second task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        Task::create([
            'board_id' => $board->id,
            'title' => 'Third task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        // Only two of the three tasks in the column: a filtered
        // board only shows some tasks, so a partial order must not
        // scramble the ones the page did not show.
        $this->post(route('tasks.reorder'), [
            'board_id' => $board->id,
            'status' => 'todo',
            'ids' => [$second->id, $first->id],
        ])->assertSessionHasErrors('ids');

        $this->assertDatabaseHas('tasks', ['id' => $first->id, 'position' => 0]);
        $this->assertDatabaseHas('tasks', ['id' => $second->id, 'position' => 0]);
    }

    public function test_reorder_rejects_an_unknown_status(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $task = Task::create([
            'board_id' => $board->id,
            'title' => 'Only task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $this->post(route('tasks.reorder'), [
            'board_id' => $board->id,
            'status' => 'archived',
            'ids' => [$task->id],
        ])->assertSessionHasErrors('status');
    }

    public function test_moving_a_task_to_another_column_appends_it_at_the_end(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $moving = Task::create([
            'board_id' => $board->id,
            'title' => 'Moving task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        $first = Task::create([
            'board_id' => $board->id,
            'title' => 'First in progress',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);
        $second = Task::create([
            'board_id' => $board->id,
            'title' => 'Second in progress',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);
        $second->position = 1;
        $second->save();

        // Same as dragging the card onto the In progress column.
        $this->patch(route('tasks.updateStatus', $moving), ['status' => 'in_progress'])
            ->assertRedirect(route('tasks.index', ['board' => $board->id]));

        // The moved task lands after the column's existing tasks,
        // so a hand-ordered column keeps its order.
        $this->assertDatabaseHas('tasks', ['id' => $moving->id, 'position' => 2]);
        $this->assertDatabaseHas('tasks', ['id' => $first->id, 'position' => 0]);
        $this->assertDatabaseHas('tasks', ['id' => $second->id, 'position' => 1]);
    }

    public function test_a_new_task_joins_the_end_of_the_todo_column(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $first = Task::create([
            'board_id' => $board->id,
            'title' => 'First task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        $second = Task::create([
            'board_id' => $board->id,
            'title' => 'Second task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
        $second->position = 1;
        $second->save();

        $this->post(route('tasks.store'), [
            'board_id' => $board->id,
            'title' => 'New task',
            'priority' => 'medium',
        ])->assertRedirect(route('tasks.index', ['board' => $board->id]));

        $new = Task::where('title', 'New task')->firstOrFail();
        $this->assertSame(2, $new->position);
        $this->assertDatabaseHas('tasks', ['id' => $first->id, 'position' => 0]);
        $this->assertDatabaseHas('tasks', ['id' => $second->id, 'position' => 1]);
    }
}
