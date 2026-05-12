<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        // Fetch tasks, ordered by deadline, with an optional status filter
        $tasks = Task::query()
            ->when($status, fn ($q) => $q->byStatus($status))
            ->byDeadline()
            ->get();

        // Get counts for the UI navigation badges
        $counts = [
            'all'         => Task::count(),
            'to_do'       => Task::byStatus('to_do')->count(),
            'in_progress' => Task::byStatus('in_progress')->count(),
            'completed'   => Task::byStatus('completed')->count(),
            'submitted'   => Task::byStatus('submitted')->count(),
        ];

        return view('tasks.index', compact('tasks', 'status', 'counts'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'deadline' => 'required|date',
            'status'   => ['required', Rule::in(array_keys(Task::STATUSES))],
        ]);

        Task::create($validated);

        return redirect()
            ->route('tasks.index')
            ->with('success', '✅ Task "' . $validated['name'] . '" created successfully!');
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'deadline' => 'required|date',
            'status'   => ['required', Rule::in(array_keys(Task::STATUSES))],
        ]);

        $task->update($validated);

        // Redirect back to the tab matching the task's new status
        return redirect()
            ->route('tasks.index', ['status' => $task->status])
            ->with('success', '✏️ Task "' . $task->name . '" updated successfully!');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        // Store the name before deletion for the success message
        $name = $task->name;
        
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', '🗑️ Task "' . $name . '" removed successfully!');
    }
}
