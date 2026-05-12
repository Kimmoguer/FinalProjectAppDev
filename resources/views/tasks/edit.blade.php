@extends('layouts.app')

@section('title', 'Edit Task')

@section('header-action')
    <a href="{{ route('tasks.index') }}" class="btn btn-ghost">
        ← Back to List
    </a>
@endsection

@section('content')

<div class="glass-card form-card">

    <div class="form-page-title">Edit Task</div>
    <p class="form-page-sub">Update the details for <strong style="color:var(--text-primary)">{{ $task->name }}</strong>.</p>

    <form action="{{ route('tasks.update', $task) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Task Name --}}
        <div class="form-group">
            <label class="form-label" for="name">Task Name</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                placeholder="e.g. Logo Design"
                value="{{ old('name', $task->name) }}"
                autofocus
            />
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Priority --}}
        <div class="form-group">
            <label class="form-label" for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control">
                @foreach(\App\Models\Task::PRIORITIES as $p)
                    <option value="{{ $p }}" {{ old('priority', $task->priority) === $p ? 'selected' : '' }}>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
            @error('priority')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Deadline --}}
        <div class="form-group">
            <label class="form-label" for="deadline">Deadline</label>
            <input
                type="date"
                id="deadline"
                name="deadline"
                class="form-control"
                value="{{ old('deadline', $task->deadline->format('Y-m-d')) }}"
            />
            @error('deadline')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Status --}}
        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-control">
                @foreach(\App\Models\Task::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $task->status) === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                💾 Save Changes
            </button>
            <a href="{{ route('tasks.index') }}" class="btn btn-ghost">
                Cancel
            </a>

            {{-- Quick delete from edit page --}}
            <form action="{{ route('tasks.destroy', $task) }}"
                  method="POST"
                  style="margin-left:auto"
                  onsubmit="return confirm('Delete this task permanently?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    🗑️ Delete
                </button>
            </form>
        </div>

    </form>

</div>

@endsection