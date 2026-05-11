@extends('layouts.app')

@section('content')


    <div class="tabs">
        <a href="?status=Home" class="tab {{ $status == 'Home' ? 'active' : '' }}">All Tasks</a>
        <a href="?status=To Do" class="tab {{ $status == 'To Do' ? 'active' : '' }}">To Do</a>
        <a href="?status=In Progress" class="tab {{ $status == 'In Progress' ? 'active' : '' }}">In Progress</a>
        <a href="?status=Completed" class="tab {{ $status == 'Completed' ? 'active' : '' }}">Completed</a>
        <a href="?status=Submitted" class="tab {{ $status == 'Submitted' ? 'active' : '' }}">Submitted</a>
    </div>

    @if($status == 'Manage Lists' || $status == 'Home')
    <div class="glass-card">
        <span class="section-label">Create New Entry</span>
        <form action="{{ route('tasks.store') }}" method="POST" style="display: grid; grid-template-columns: minmax(200px, 2fr) minmax(120px, 1fr) minmax(150px, 1fr) auto; gap: 1.5rem; align-items: flex-end;">
            @csrf
            <div>
                <label>Task Description</label>
                <input type="text" name="task_name" required placeholder="What needs to be done?">
            </div>
            <div>
                <label>Priority</label>
                <select name="priority" required>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
            </div>
            <div>
                <label>Deadline <span style="font-weight: 400; font-size: 0.75rem; color: var(--text-muted);">(Optional)</span></label>
                <input type="date" name="deadline">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
    @endif

    <span class="section-label">Task Registry</span>
    <div class="task-list">
        @forelse($tasks as $index => $task)
            <div class="task-item">
                <div class="task-sn">#{{ $index + 1 }}</div>
                <div class="task-name">{{ $task->task_name }}</div>
                <div><span class="badge badge-{{ $task->priority }}">{{ $task->priority }}</span></div>
                <div><span class="badge badge-status" style="font-size: 0.65rem; padding: 0.25rem 0.5rem;">{{ $task->status }}</span></div>
                <div class="task-deadline">
                    @if($task->deadline)
                        {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y') }}
                    @else
                        <span style="opacity: 0.3;">No deadline</span>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-success btn-sm">Edit</a>
                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Delete this task?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="glass-card" style="text-align: center; color: var(--text-muted); padding: 4rem;">
                <p style="font-weight: 600; font-size: 1.125rem;">No tasks in this view.</p>
                <p style="font-size: 0.875rem; margin-top: 0.5rem;">Get started by creating a new task above.</p>
            </div>
        @endforelse
    </div>
@endsection
