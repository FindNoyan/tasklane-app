<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_lists_tasks_by_due_date_then_priority_with_no_due_date_last(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $task = fn (string $title, ?string $dueDate, string $priority) => Task::create([
            'board_id' => $board->id,
            'title' => $title,
            'status' => 'todo',
            'priority' => $priority,
            'due_date' => $dueDate,
        ]);

        // Same due date, different priority: high must come first.
        $task('Later low priority', Carbon::today()->addDays(2)->toDateString(), 'low');
        $task('Later high priority', Carbon::today()->addDays(2)->toDateString(), 'high');
        // A later due date, even at medium priority, comes after both.
        $task('Even later medium priority', Carbon::today()->addDays(5)->toDateString(), 'medium');
        // No due date goes last, even at high priority.
        $task('No due date high priority', null, 'high');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeInOrder([
            'Later high priority',
            'Later low priority',
            'Even later medium priority',
            'No due date high priority',
        ]);
    }
}
