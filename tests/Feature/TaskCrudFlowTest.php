<?php

namespace Tests\Feature;

use App\Models\Board;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_task_appears_on_board_with_flash(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $response = $this->post('/tasks', [
            'board_id' => $board->id,
            'title' => 'Board visible task',
            'description' => 'Shown on the board',
            'priority' => 'high',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status', 'Task added.');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Board visible task',
            'status' => 'todo',
        ]);

        // Creating a task is only done when it actually shows up
        // where the user looks: on the board itself.
        $this->get('/')->assertOk()
            ->assertSee('Board visible task')
            ->assertSee('Shown on the board');
    }
}
