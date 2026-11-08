<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardSwitchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_board_opens_it_and_lists_it_in_the_switcher(): void
    {
        Board::firstOrCreate(['name' => 'My Board']);

        $response = $this->post('/boards', ['name' => 'Work']);

        $board = Board::where('name', 'Work')->firstOrFail();

        $response->assertRedirect(route('tasks.index', ['board' => $board->id]));
        $response->assertSessionHas('status', 'Board created.');

        // The new board is open and both boards are listed in the
        // switcher, so the user can move between them.
        $this->get(route('tasks.index', ['board' => $board->id]))->assertOk()
            ->assertSee('My Board')
            ->assertSee('Work')
            ->assertSee('New board');
    }

    public function test_switching_boards_shows_only_that_boards_tasks(): void
    {
        $first = Board::firstOrCreate(['name' => 'My Board']);
        $second = Board::create(['name' => 'Work']);

        Task::create([
            'board_id' => $first->id,
            'title' => 'First board task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        Task::create([
            'board_id' => $second->id,
            'title' => 'Second board task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $this->get(route('tasks.index', ['board' => $second->id]))->assertOk()
            ->assertSee('Second board task')
            ->assertDontSee('First board task');

        $this->get('/')->assertOk()
            ->assertSee('First board task')
            ->assertDontSee('Second board task');
    }

    public function test_creating_a_task_redirects_back_to_its_board(): void
    {
        Board::firstOrCreate(['name' => 'My Board']);
        $second = Board::create(['name' => 'Work']);

        $this->post('/tasks', [
            'board_id' => $second->id,
            'title' => 'Work task',
            'priority' => 'high',
        ])->assertRedirect(route('tasks.index', ['board' => $second->id]));
    }

    public function test_unknown_or_invalid_board_id_falls_back_to_the_first_board(): void
    {
        Board::firstOrCreate(['name' => 'My Board']);

        Task::create([
            'board_id' => Board::first()->id,
            'title' => 'First board task',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        // A stale bookmark or a mistyped id must show a board, not
        // an error page.
        $this->get('/?board=999999')->assertOk()
            ->assertSee('First board task');

        // An array value (?board[]=1) must fall back the same way,
        // not hand a Collection to the view as if it were a Board.
        $this->get('/?board[]=1')->assertOk()
            ->assertSee('First board task');
    }

    public function test_duplicate_board_name_is_rejected(): void
    {
        Board::firstOrCreate(['name' => 'My Board']);
        Board::create(['name' => 'Work']);

        $this->post('/boards', ['name' => 'Work'])
            ->assertSessionHasErrors('name');

        // Two boards with the same name would be indistinguishable
        // in the switcher, so the second one is never created.
        $this->assertDatabaseCount('boards', 2);
    }

    public function test_empty_board_name_is_rejected(): void
    {
        Board::firstOrCreate(['name' => 'My Board']);

        $this->post('/boards', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('boards', 1);
    }
}
