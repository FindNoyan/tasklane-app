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
     * Order tasks the way a board column should read: a hand-set
     * order first (position — 0 for every task until its column is
     * reordered by drag and drop, then 0..n in the dropped order),
     * then earliest due date first, tasks with no due date last,
     * then priority high before medium before low, then creation
     * order as a stable tie-breaker. The CASE expressions are plain
     * SQL, so the order is the same on SQLite, MySQL, and PostgreSQL
     * (unlike a bare ORDER BY due_date, where nulls sort first on
     * some databases and last on others, and unlike ordering the
     * string priority alphabetically, which would put high last).
     * Because unreordered tasks all share position 0, their
     * relative order is exactly the old due-date / priority order.
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->orderBy('id');
    }

    /**
     * Narrow tasks by the board's search + filter form. Unknown
     * values are ignored, never an error: an unknown priority simply
     * means "no priority filter", and an empty search means "no
     * search". The overdue rule matches isOverdue(): due before
     * today and not done, so a task due today is never overdue.
     */
    public function scopeFiltered(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            // LOWER() on both sides, not a bare LIKE: LIKE is
            // case-insensitive on MySQL's default collation but
            // case-sensitive on PostgreSQL, and this board must
            // behave the same on either database.
            $query->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($search).'%']);
        }

        $priority = $filters['priority'] ?? null;

        if (in_array($priority, ['low', 'medium', 'high'], true)) {
            $query->where('priority', $priority);
        }

        if (! empty($filters['overdue'])) {
            $query->where('due_date', '<', today()->toDateString())
                ->where('status', '!=', 'done');
        }
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
