<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
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

    public function test_edit_page_is_prefilled_and_update_shows_on_board(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $task = Task::create([
            'board_id' => $board->id,
            'title' => 'Task to edit',
            'description' => 'Original description',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        // The edit form must start from the saved values, not blank.
        $this->get("/tasks/{$task->id}/edit")->assertOk()
            ->assertSee('Edit task')
            ->assertSee('Task to edit')
            ->assertSee('Original description')
            ->assertSee('Save changes');

        $response = $this->put("/tasks/{$task->id}", [
            'title' => 'Edited task',
            'description' => 'Updated description',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status', 'Task updated.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Edited task',
            'description' => 'Updated description',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $this->get('/')->assertOk()
            ->assertSee('Edited task')
            ->assertSee('Updated description')
            ->assertDontSee('Original description');
    }

    public function test_column_move_changes_only_status_and_rejects_invalid_status(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $task = Task::create([
            'board_id' => $board->id,
            'title' => 'Task to move',
            'description' => 'Stays put on a move',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        // A column move is the board's main interaction, and it had
        // no test at all: todo -> in progress -> done.
        $this->patch("/tasks/{$task->id}", ['status' => 'in_progress'])
            ->assertRedirect('/')
            ->assertSessionHas('status', 'Task updated.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Task to move',
            'description' => 'Stays put on a move',
            'status' => 'in_progress',
        ]);

        $this->patch("/tasks/{$task->id}", ['status' => 'done'])
            ->assertRedirect('/');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'done',
        ]);

        // An invalid column must not move the task anywhere.
        $this->patch("/tasks/{$task->id}", ['status' => 'archived'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Task to move',
            'status' => 'done',
        ]);
    }
}
