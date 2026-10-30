<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_rejects_missing_title_bad_priority_and_unknown_board(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $this->post('/tasks', [
            'board_id' => $board->id,
            'title' => '',
            'priority' => 'high',
        ])->assertSessionHasErrors('title');

        $this->post('/tasks', [
            'board_id' => $board->id,
            'title' => 'Bad priority',
            'priority' => 'urgent',
        ])->assertSessionHasErrors('priority');

        $this->post('/tasks', [
            'board_id' => 999999,
            'title' => 'Unknown board',
            'priority' => 'high',
        ])->assertSessionHasErrors('board_id');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_store_accepts_valid_task_and_update_rejects_bad_status(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $this->post('/tasks', [
            'board_id' => $board->id,
            'title' => 'Valid task',
            'priority' => 'medium',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Valid task',
            'status' => 'todo',
        ]);

        $task = Task::first();

        $this->put("/tasks/{$task->id}", [
            'title' => 'Valid task',
            'status' => 'archived',
            'priority' => 'medium',
        ])->assertSessionHasErrors('status');

        $this->put("/tasks/{$task->id}", [
            'title' => 'Renamed task',
            'status' => 'done',
            'priority' => 'low',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Renamed task',
            'status' => 'done',
        ]);
    }
}
