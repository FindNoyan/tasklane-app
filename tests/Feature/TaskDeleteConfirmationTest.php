<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDeleteConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_marks_the_delete_form_for_confirmation(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        Task::create([
            'board_id' => $board->id,
            'title' => 'Task to keep safe',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-confirm-delete', false);
    }

    public function test_confirmed_delete_still_removes_the_task(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $task = Task::create([
            'board_id' => $board->id,
            'title' => 'Task to remove',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index', ['board' => $board->id]));
        $response->assertSessionHas('status', 'Task removed.');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}

