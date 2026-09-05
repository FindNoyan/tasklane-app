<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $board = Board::firstOrCreate(['name' => 'My Board']);

        $tasks = [
            ['title' => 'Set up project repo', 'status' => 'done', 'priority' => 'medium'],
            ['title' => 'Design the board layout', 'status' => 'done', 'priority' => 'high'],
            ['title' => 'Build task creation form', 'status' => 'in_progress', 'priority' => 'high'],
            ['title' => 'Wire up status updates', 'status' => 'in_progress', 'priority' => 'medium'],
            ['title' => 'Write onboarding notes', 'status' => 'todo', 'priority' => 'low'],
            ['title' => 'Add due-date reminders', 'status' => 'todo', 'priority' => 'medium'],
        ];

        foreach ($tasks as $task) {
            Task::firstOrCreate(
                ['board_id' => $board->id, 'title' => $task['title']],
                $task + ['board_id' => $board->id]
            );
        }
    }
}
