<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'priority',
        'deadline',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'date',
    ];

    /**
     * Scope a query to only include tasks of a given status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to order by deadline ascending.
     */
    public function scopeByDeadline($query)
    {
        return $query->orderBy('deadline');
    }

    /**
     * Get the task's human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'to_do'       => 'To Do',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'submitted'   => 'Submitted',
            default       => ucfirst($this->status),
        };
    }

    /**
     * Get the CSS class name for the priority badge.
     */
    public function getPriorityClassAttribute(): string
    {
        return match ($this->priority) {
            'High'   => 'priority-high',
            'Medium' => 'priority-medium',
            'Low'    => 'priority-low',
            default  => 'priority-medium',
        };
    }

    /**
     * Available task statuses.
     */
    public const STATUSES = [
        'to_do'       => 'To Do',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'submitted'   => 'Submitted',
    ];

    /**
     * Available task priorities.
     */
    public const PRIORITIES = ['High', 'Medium', 'Low'];
}
