@extends('layouts.app')

@section('content')
    <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
        <span class="section-label">Management</span>
        <h2 style="margin-bottom: 2rem; color: #fff; font-size: 1.5rem; font-weight: 800;">Update Task Details</h2>
        
        <form action="{{ route('tasks.update', $task->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 1.5rem;">
                <label>Task Description</label>
                <input type="text" name="task_name" value="{{ old('task_name', $task->task_name) }}" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label>Priority</label>
                    <select name="priority" required>
                        <option value="High" {{ $task->priority == 'High' ? 'selected' : '' }}>High</option>
                        <option value="Medium" {{ $task->priority == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low" {{ $task->priority == 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status" required>
                        <option value="To Do" {{ $task->status == 'To Do' ? 'selected' : '' }}>To Do</option>
                        <option value="In Progress" {{ $task->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed" {{ $task->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Submitted" {{ $task->status == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-bottom: 2.5rem;">
                <label>Deadline <span style="font-weight: 400; font-size: 0.75rem; color: var(--text-muted);">(Optional)</span></label>
                <input type="date" name="deadline" value="{{ old('deadline', $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') : '') }}">
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                <a href="{{ route('tasks.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: var(--text-muted); flex: 1;">Discard</a>
            </div>
        </form>
    </div>
@endsection