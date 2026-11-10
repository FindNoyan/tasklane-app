<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_rename_board_updates_name_and_opens_it(): void
    {
        $board = Board::create(['name' => 'Work']);

        $this->get("/boards/{$board->id}/edit")->assertOk()
            ->assertSee('Rename board')
            ->assertSee('Work')
            ->assertSee('Save changes');

        $response = $this->put("/boards/{$board->id}", ['name' => 'Personal']);

        $response->assertRedirect(route('tasks.index', ['board' => $board->id]));
        $response->assertSessionHas('status', 'Board renamed.');

        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'Personal',
        ]);

        // The renamed board is open and listed under its new name.
        $this->get(route('tasks.index', ['board' => $board->id]))->assertOk()
            ->assertSee('Personal')
            ->assertDontSee('Work');
    }

    public function test_rename_to_another_boards_name_is_rejected_but_keeping_its_own_is_allowed(): void
    {
        Board::firstOrCreate(['name' => 'My Board']);
        $board = Board::create(['name' => 'Work']);

        $this->put("/boards/{$board->id}", ['name' => 'My Board'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'Work',
        ]);

        // Renaming a board to its own current name is not a
        // duplicate — the uniqueness rule ignores the board itself.
        $this->put("/boards/{$board->id}", ['name' => 'Work'])
            ->assertRedirect(route('tasks.index', ['board' => $board->id]));
    }

    public function test_rename_with_empty_name_is_rejected(): void
    {
        $board = Board::create(['name' => 'Work']);

        $this->put("/boards/{$board->id}", ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'Work',
        ]);
    }

    public function test_delete_board_removes_its_tasks_and_keeps_other_boards(): void
    {
        $first = Board::firstOrCreate(['name' => 'My Board']);
        $second = Board::create(['name' => 'Work']);

        Task::create([
            'board_id' => $first->id,
            'title' => 'Kept task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        Task::create([
            'board_id' => $second->id,
            'title' => 'Deleted task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $response = $this->delete("/boards/{$second->id}");

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('status', 'Board deleted.');

        // The board is gone, and its tasks went with it — the
        // tasks table cascades on board delete, so nothing is
        // orphaned. The other board and its task are untouched.
        $this->assertDatabaseCount('boards', 1);
        $this->assertDatabaseMissing('boards', ['name' => 'Work']);
        $this->assertDatabaseMissing('tasks', ['title' => 'Deleted task']);
        $this->assertDatabaseHas('tasks', ['title' => 'Kept task']);

        $this->get('/')->assertOk()
            ->assertSee('Kept task')
            ->assertDontSee('Deleted task');
    }

    public function test_delete_last_board_still_shows_a_working_board_page(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        Task::create([
            'board_id' => $board->id,
            'title' => 'Deleted task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $this->delete("/boards/{$board->id}")
            ->assertRedirect(route('tasks.index'));

        // Deleting the only board recreates the default board on the
        // next visit, so the page never breaks — it just starts empty.
        $this->get('/')->assertOk()
            ->assertSee('My Board')
            ->assertDontSee('Deleted task');
    }

    public function test_move_task_to_another_board_from_the_edit_page(): void
    {
        $first = Board::firstOrCreate(['name' => 'My Board']);
        $second = Board::create(['name' => 'Work']);

        $task = Task::create([
            'board_id' => $first->id,
            'title' => 'Moving task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        // The edit form offers every board, with the task's own
        // board selected.
        $this->get("/tasks/{$task->id}/edit")->assertOk()
            ->assertSee('My Board')
            ->assertSee('Work');

        $response = $this->put("/tasks/{$task->id}", [
            'board_id' => $second->id,
            'title' => 'Moving task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        // Saving opens the board the task now belongs to.
        $response->assertRedirect(route('tasks.index', ['board' => $second->id]));
        $response->assertSessionHas('status', 'Task updated.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'board_id' => $second->id,
            'title' => 'Moving task',
        ]);

        $this->get(route('tasks.index', ['board' => $second->id]))->assertOk()
            ->assertSee('Moving task');

        $this->get(route('tasks.index', ['board' => $first->id]))->assertOk()
            ->assertDontSee('Moving task');
    }

    public function test_move_task_to_unknown_board_is_rejected(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $task = Task::create([
            'board_id' => $board->id,
            'title' => 'Staying task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $this->put("/tasks/{$task->id}", [
            'board_id' => 999999,
            'title' => 'Staying task',
            'status' => 'todo',
            'priority' => 'medium',
        ])->assertSessionHasErrors('board_id');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'board_id' => $board->id,
        ]);
    }
}
