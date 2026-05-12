<?php

/**
 * ============================================================
 *  FILE: app/Models/Task.php
 * ============================================================
 *  This is the ELOQUENT MODEL for the Task.
 *
 *  In Laravel, a Model represents ONE database table.
 *  Every row in the `tasks` table becomes a Task PHP object.
 *
 *  Eloquent ORM replaces raw SQL with clean PHP:
 *    Task::all()           => SELECT * FROM tasks
 *    Task::create([...])   => INSERT INTO tasks ...
 *    $task->update([...])  => UPDATE tasks SET ...
 *    $task->delete()       => DELETE FROM tasks WHERE id = ?
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /*
     * HasFactory enables Laravel to auto-generate fake Task
     * objects for testing (used by RefreshDatabase in tests).
     */
    use HasFactory;

    // ----------------------------------------------------------
    //  MASS-ASSIGNMENT GUARD
    // ----------------------------------------------------------
    /**
     * $fillable is a SECURITY whitelist.
     *
     * Only columns listed here can be set via:
     *   Task::create([...]) or $task->update([...])
     *
     * This blocks attackers from injecting unlisted fields
     * (e.g. "is_admin=1") through POST data.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',      // Task title,  e.g. "Logo Design"
        'priority',  // 'High' | 'Medium' | 'Low'
        'deadline',  // Date string, e.g. "2026-12-31"
        'status',    // 'to_do' | 'in_progress' | 'completed' | 'submitted'
    ];

    // ----------------------------------------------------------
    //  ATTRIBUTE CASTING
    // ----------------------------------------------------------
    /**
     * $casts converts raw DB values into proper PHP types.
     *
     * Casting `deadline` to 'date' wraps it in a Carbon object:
     *   $task->deadline->format('Y-m-d')  -- clean date string
     *   $task->deadline->isPast()         -- boolean check
     *   $task->deadline->diffForHumans()  -- "3 days ago"
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'date',
    ];

    // ----------------------------------------------------------
    //  ELOQUENT QUERY SCOPES
    // ----------------------------------------------------------
    /**
     * A "local scope" is a reusable WHERE clause.
     * Laravel strips the "scope" prefix when you call it.
     *
     * USAGE:
     *   Task::byStatus('to_do')->get()
     * SQL GENERATED:
     *   SELECT * FROM tasks WHERE status = 'to_do'
     *
     * Scopes can be chained with other scopes:
     *   Task::byStatus('to_do')->byDeadline()->get()
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: order results by deadline ascending (soonest first).
     *
     * USAGE:
     *   Task::byDeadline()->get()
     * SQL GENERATED:
     *   SELECT * FROM tasks ORDER BY deadline ASC
     */
    public function scopeByDeadline($query)
    {
        return $query->orderBy('deadline');
    }

    // ----------------------------------------------------------
    //  ACCESSOR ATTRIBUTES  (virtual / computed properties)
    // ----------------------------------------------------------
    /**
     * An "accessor" is a virtual read-only property on the model.
     * Naming format:  get{PropertyName}Attribute
     *
     * This converts the raw DB value ('in_progress')
     * into a human-readable label ('In Progress') for the view.
     *
     * USAGE IN BLADE:
     *   {{ $task->statusLabel }}
     */
    public function getStatusLabelAttribute(): string
    {
        // PHP 8 match() -- like switch, but strict + returns value
        return match ($this->status) {
            'to_do'       => 'To Do',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'submitted'   => 'Submitted',
            default       => ucfirst($this->status),
        };
    }

    /**
     * Returns the CSS class name for the priority badge colour.
     * The actual CSS colour rules live in layouts/app.blade.php.
     *
     * USAGE IN BLADE:
     *   <span class="badge {{ $task->priorityClass }}">
     */
    public function getPriorityClassAttribute(): string
    {
        return match ($this->priority) {
            'High'   => 'priority-high',    // red badge
            'Medium' => 'priority-medium',  // amber badge
            'Low'    => 'priority-low',     // green badge
            default  => 'priority-medium',
        };
    }

    // ----------------------------------------------------------
    //  CONSTANTS
    //  Single source of truth -- change here, changes everywhere
    // ----------------------------------------------------------
    /**
     * All valid task statuses.
     * Key   = value stored in the database
     * Value = human-readable label shown in the UI
     *
     * Used in:
     *  - Controller validation: Rule::in(array_keys(Task::STATUSES))
     *  - Blade <select> loop:   @foreach(Task::STATUSES as $val => $label)
     *  - Tab badges in index:   Task::STATUSES[$status]
     */
    public const STATUSES = [
        'to_do'       => 'To Do',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'submitted'   => 'Submitted',
    ];

    /**
     * Valid priority levels.
     * Used in controller validation: Rule::in(Task::PRIORITIES)
     */
    public const PRIORITIES = ['High', 'Medium', 'Low'];
}