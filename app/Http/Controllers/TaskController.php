<?php

/**
 * ============================================================
 *  FILE: app/Http/Controllers/TaskController.php
 * ============================================================
 *  This is the CONTROLLER — the "traffic director" of the app.
 *
 *  When a user hits a URL or submits a form, Laravel routes
 *  the request here. The controller:
 *    1. Receives the HTTP request
 *    2. Calls the Eloquent Model (Task) to read/write the DB
 *    3. Returns a Blade VIEW or a REDIRECT response
 *
 *  MVC Pattern:
 *    Model      = Task.php            (data layer)
 *    View       = resources/views/    (presentation layer)
 *    Controller = THIS FILE           (logic / glue layer)
 *
 *  CRUD Map (RESTful):
 *    index()   READ   -- list all tasks
 *    create()  READ   -- show the blank "Add Task" form
 *    store()   CREATE -- save a new task (INSERT)
 *    edit()    READ   -- show the "Edit Task" form (pre-filled)
 *    update()  UPDATE -- save edits to an existing task
 *    destroy() DELETE -- permanently remove a task
 * ============================================================
 */

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    // =========================================================
    //  INDEX  --  Read & list tasks
    // =========================================================

    /**
     * ROUTES:
     *   GET /tasks           -- show ALL tasks (Home tab)
     *   GET /tasks?status=   -- filter by a specific status tab
     *
     * Eloquent ORM calls made here:
     *   Task::query()             -- start a builder (like SELECT ...)
     *   ->when(...)               -- conditionally add WHERE
     *   ->byStatus($status)       -- our scope: WHERE status = ?
     *   ->byDeadline()            -- our scope: ORDER BY deadline ASC
     *   ->get()                   -- execute query, return Collection
     *   Task::count()             -- SELECT COUNT(*) FROM tasks
     *   Task::byStatus(...)->count() -- COUNT with a WHERE
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Grab the ?status= query string. Null means "show all".
        $status = $request->query('status');

        /*
         * BUILD THE TASK LIST QUERY
         *
         * Task::query()
         *   Opens a fresh Eloquent query builder on the tasks table.
         *   Equivalent to starting with: "SELECT * FROM tasks"
         *
         * ->when($status, fn($q) => $q->byStatus($status))
         *   Only appends WHERE status = ? when $status is not null.
         *   This is cleaner than an if/else block.
         *
         * ->byDeadline()
         *   Applies the scope that adds ORDER BY deadline ASC.
         *
         * ->get()
         *   Fires the SQL and returns an Eloquent Collection
         *   (a feature-rich iterable of Task objects).
         */
        $tasks = Task::query()
            ->when($status, fn ($q) => $q->byStatus($status))
            ->byDeadline()
            ->get();

        /*
         * COUNT BADGES
         * One COUNT query per tab so the nav shows live numbers.
         */
        $counts = [
            'all'         => Task::count(),
            'to_do'       => Task::byStatus('to_do')->count(),
            'in_progress' => Task::byStatus('in_progress')->count(),
            'completed'   => Task::byStatus('completed')->count(),
            'submitted'   => Task::byStatus('submitted')->count(),
        ];

        /*
         * compact('tasks', 'status', 'counts') is shorthand for:
         *   ['tasks' => $tasks, 'status' => $status, 'counts' => $counts]
         * These variables become available inside the Blade view.
         */
        return view('tasks.index', compact('tasks', 'status', 'counts'));
    }

    // =========================================================
    //  CREATE  --  Show the blank "Add Task" form
    // =========================================================

    /**
     * ROUTE:  GET /tasks/create
     *
     * Just renders the empty form.  No DB call needed because
     * we are not loading any existing data -- just showing fields.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('tasks.create');
    }

    // =========================================================
    //  STORE  --  Validate then save a NEW task  (INSERT)
    // =========================================================

    /**
     * ROUTE:  POST /tasks
     * Called when the user submits the "Add Task" form.
     *
     * Eloquent ORM call:
     *   Task::create($validated)
     *   SQL: INSERT INTO tasks (name, priority, deadline, status, ...)
     *              VALUES (?, ?, ?, ?, ...)
     *
     * @param  \Illuminate\Http\Request  $request  POST form data
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /*
         * VALIDATION
         * ----------
         * validate() checks every rule against the incoming POST data.
         *
         * If ANY rule FAILS:
         *   - Laravel automatically redirects back to the form
         *   - Error messages are flashed to the session
         *   - Old input values are preserved (old() in Blade)
         *   - The code below this block NEVER runs
         *
         * If ALL rules PASS:
         *   - $validated holds only the safe, validated fields
         *   - Code continues to the Eloquent create() below
         *
         * Rule breakdown:
         *   'required'   -- field must not be empty
         *   'string'     -- must be a string type
         *   'max:255'    -- no longer than 255 characters
         *   'date'       -- must be parseable as a valid date
         *   Rule::in()   -- value must be one of the listed options
         */
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'deadline' => 'required|date',
            'status'   => ['required', Rule::in(array_keys(Task::STATUSES))],
        ]);

        /*
         * ELOQUENT  CREATE  (INSERT)
         * --------------------------
         * Task::create($validated):
         *   1. Makes a new Task instance
         *   2. Assigns only $fillable columns from $validated
         *   3. Calls save() which fires the INSERT SQL
         *   4. Returns the new Task object with its generated id
         *
         * This is pure Eloquent ORM -- no raw SQL anywhere!
         */
        Task::create($validated);

        /*
         * After saving, redirect to the task list.
         * ->with('success', ...) flashes a one-time session message
         * that the Blade layout shows as a green banner.
         */
        return redirect()
            ->route('tasks.index')
            ->with('success', '✅ Task "' . $validated['name'] . '" created successfully!');
    }

    // =========================================================
    //  EDIT  --  Show the "Edit Task" form (pre-filled)
    // =========================================================

    /**
     * ROUTE:  GET /tasks/{task}/edit
     *
     * {task} in the URL is resolved automatically by Laravel's
     * "Route Model Binding":
     *
     *   Laravel sees `Task $task` in the method signature,
     *   looks up  Task::find($id)  using the URL segment,
     *   and injects the matching Task object directly.
     *   If no Task with that ID exists -> automatic 404.
     *
     * SQL generated behind the scenes:
     *   SELECT * FROM tasks WHERE id = ? LIMIT 1
     *
     * @param  \App\Models\Task  $task  auto-resolved by Laravel
     * @return \Illuminate\View\View
     */
    public function edit(Task $task)
    {
        // Pass the found task to the view so form fields are pre-filled
        return view('tasks.edit', compact('task'));
    }

    // =========================================================
    //  UPDATE  --  Validate then save changes  (UPDATE)
    // =========================================================

    /**
     * ROUTE:  PUT /tasks/{task}
     *
     * HTML forms only support GET/POST, so Blade's @method('PUT')
     * directive adds a hidden _method=PUT field, and Laravel
     * reads it to treat the request as a PUT.
     *
     * Eloquent ORM call:
     *   $task->update($validated)
     *   SQL: UPDATE tasks
     *        SET name=?, priority=?, deadline=?, status=?
     *        WHERE id = ?
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task          $task   route model binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Task $task)
    {
        // Same validation rules as store()
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'deadline' => 'required|date',
            'status'   => ['required', Rule::in(array_keys(Task::STATUSES))],
        ]);

        /*
         * ELOQUENT  UPDATE
         * ----------------
         * $task->update($validated):
         *   1. Assigns new values to the model attributes
         *   2. Calls save() -> fires the UPDATE SQL
         *   3. Only updates $fillable columns (security guard)
         *
         * KEY DIFFERENCE vs store():
         *   store() uses  Task::create()  -- creates a NEW row
         *   update() uses $task->update() -- modifies EXISTING row
         */
        $task->update($validated);

        // Redirect back to the tab matching the task's new status
        return redirect()
            ->route('tasks.index', ['status' => $task->status])
            ->with('success', '✏️ Task "' . $task->name . '" updated successfully!');
    }

    // =========================================================
    //  DESTROY  --  Delete a task  (DELETE)
    // =========================================================

    /**
     * ROUTE:  DELETE /tasks/{task}
     *
     * Same HTML trick as PUT -- Blade's @method('DELETE') adds
     * a hidden _method=DELETE field.
     *
     * Eloquent ORM call:
     *   $task->delete()
     *   SQL: DELETE FROM tasks WHERE id = ?
     *
     * @param  \App\Models\Task  $task  route model binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Task $task)
    {
        // Store the name BEFORE deleting (once deleted, $task is gone)
        $name = $task->name;

        /*
         * ELOQUENT  DELETE
         * ----------------
         * $task->delete() fires:
         *   DELETE FROM tasks WHERE id = ?
         *
         * The row is permanently removed from the database.
         * After this line, $task no longer maps to any DB record.
         */
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', '🗑️ Task "' . $name . '" removed successfully!');
    }
}