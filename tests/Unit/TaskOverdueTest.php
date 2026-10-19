<?php

namespace Tests\Unit;

use App\Models\Task;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TaskOverdueTest extends TestCase
{
    private function task(?string $dueDate, string $status = 'todo'): Task
    {
        return new Task([
            'due_date' => $dueDate,
            'status' => $status,
        ]);
    }

    public function test_task_due_yesterday_is_overdue(): void
    {
        $this->assertTrue($this->task(Carbon::yesterday()->toDateString())->isOverdue());
    }

    public function test_task_due_today_is_not_overdue(): void
    {
        // Regression: with isPast() on a midnight-cast date, a task due
        // today was flagged overdue. Comparing calendar dates fixes it.
        $this->assertFalse($this->task(Carbon::today()->toDateString())->isOverdue());
    }

    public function test_task_due_tomorrow_is_not_overdue(): void
    {
        $this->assertFalse($this->task(Carbon::tomorrow()->toDateString())->isOverdue());
    }

    public function test_done_task_is_never_overdue(): void
    {
        $this->assertFalse($this->task(Carbon::yesterday()->toDateString(), 'done')->isOverdue());
    }

    public function test_task_without_due_date_is_not_overdue(): void
    {
        $this->assertFalse($this->task(null)->isOverdue());
    }

    public function test_is_due_today(): void
    {
        $this->assertTrue($this->task(Carbon::today()->toDateString())->isDueToday());
        $this->assertFalse($this->task(Carbon::yesterday()->toDateString())->isDueToday());
        $this->assertFalse($this->task(Carbon::tomorrow()->toDateString())->isDueToday());
        $this->assertFalse($this->task(null)->isDueToday());
    }
}
