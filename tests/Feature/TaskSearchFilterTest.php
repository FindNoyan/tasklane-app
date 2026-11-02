<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makeTask(string $title, string $priority, string $status, ?string $dueDate): Task
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        return Task::create([
            'board_id' => $board->id,
            'title' => $title,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate,
        ]);
    }

    public function test_search_by_title_shows_only_matching_tasks(): void
    {
        $this->makeTask('Fix login bug', 'high', 'todo', null);
        $this->makeTask('Write release notes', 'low', 'todo', null);

        $response = $this->get('/?search=login');

        $response->assertOk();
        $response->assertSee('Fix login bug');
        $response->assertDontSee('Write release notes');
    }

    public function test_priority_filter_shows_only_that_priority(): void
    {
        $this->makeTask('Urgent fix', 'high', 'todo', null);
        $this->makeTask('Tidy up docs', 'low', 'todo', null);

        $response = $this->get('/?priority=high');

        $response->assertOk();
        $response->assertSee('Urgent fix');
        $response->assertDontSee('Tidy up docs');
    }

    public function test_overdue_filter_shows_only_not_done_tasks_due_before_today(): void
    {
        $yesterday = now()->subDay()->toDateString();
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $this->makeTask('Overdue work', 'medium', 'todo', $yesterday);
        $this->makeTask('Due today work', 'medium', 'todo', $today);
        $this->makeTask('Done late work', 'medium', 'done', $yesterday);
        $this->makeTask('Future work', 'medium', 'todo', $tomorrow);

        $response = $this->get('/?overdue=1');

        $response->assertOk();
        $response->assertSee('Overdue work');
        $response->assertDontSee('Due today work');
        $response->assertDontSee('Done late work');
        $response->assertDontSee('Future work');
    }

    public function test_search_and_priority_filters_combine(): void
    {
        $this->makeTask('Fix login bug', 'high', 'todo', null);
        $this->makeTask('Fix typo in docs', 'low', 'todo', null);

        $response = $this->get('/?search=fix&priority=high');

        $response->assertOk();
        $response->assertSee('Fix login bug');
        $response->assertDontSee('Fix typo in docs');
    }

    public function test_unknown_priority_is_ignored_not_an_error(): void
    {
        $this->makeTask('First task', 'high', 'todo', null);
        $this->makeTask('Second task', 'low', 'todo', null);

        $response = $this->get('/?priority=bogus');

        $response->assertOk();
        $response->assertSee('First task');
        $response->assertSee('Second task');
    }

    public function test_filtered_board_with_no_match_explains_itself(): void
    {
        $this->makeTask('Only task', 'medium', 'todo', null);

        $response = $this->get('/?search=nothing-matches-this');

        $response->assertOk();
        $response->assertSee('No tasks match your filters.');
        $response->assertSee('Clear filters');
    }
}
