<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function isOverdue(): bool
    {
        // Compare calendar dates, not datetimes: due_date is cast to
        // `date` (midnight), so isPast() flagged a task due today as
        // overdue. A task is overdue only once its due date is behind us.
        return $this->due_date !== null
            && $this->due_date->isBefore(today())
            && $this->status !== 'done';
    }

    public function isDueToday(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isToday();
    }
}
