<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'Manage Lists');
        if ($status === 'Manage Lists' || $status === 'Home') {
            $tasks = Task::all();
        } else {
            $tasks = Task::where('status', $status)->get();
        }
        return view('tasks.index', compact('tasks', 'status'));
    }

    public function create()
    {
        // Not used, modal or inline form instead.
    }

    public function store(Request $request)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'priority' => 'required|string|in:Low,Medium,High',
            'deadline' => 'nullable|date'
        ]);

        Task::create([
            'task_name' => $request->task_name,
            'priority' => $request->priority,
            'deadline' => $request->deadline,
            'status' => 'To Do' // default status
        ]);

        return redirect()->back()->with('success', 'Task created successfully.');
    }

    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'priority' => 'required|string|in:Low,Medium,High',
            'deadline' => 'nullable|date',
            'status' => 'required|string|in:To Do,In Progress,Completed,Submitted'
        ]);

        $task->update($request->all());

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted successfully.');
    }
}
