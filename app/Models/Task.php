<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Order tasks the way a board column should read: earliest due
     * date first, tasks with no due date last, then priority high
     * before medium before low, then creation order as a stable
     * tie-breaker. The CASE expressions are plain SQL, so the order
     * is the same on SQLite, MySQL, and PostgreSQL (unlike a bare
     * ORDER BY due_date, where nulls sort first on some databases
     * and last on others, and unlike ordering the string priority
     * alphabetically, which would put high last).
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->orderBy('id');
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
