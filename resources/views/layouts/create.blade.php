{{--
================================================================================
FILE: resources/views/tasks/create.blade.php
================================================================================

PURPOSE:
  This is the "Add New Task" FORM page.
  It presents four input fields (name, priority, deadline, status).
  When submitted, the form POSTs data to TaskController@store,
  which validates and saves it via Eloquent ORM.

HOW FORM SUBMISSION WORKS:
  1. User fills in the form and clicks "Create Task"
  2. Browser sends a POST request to /tasks with the form data
  3. routes/web.php directs it to TaskController@store()
  4. The controller validates the data
  5. If valid   → Task::create() saves to DB, redirects to task list
  6. If invalid → redirects BACK to this form with $errors populated

BLADE VALIDATION HELPERS:
  @error('field')  → shows an error block if validation failed for that field
  old('field')     → re-fills the input with what the user typed before the error
                      so they don't have to retype everything
================================================================================
--}}

@extends('layouts.app')

@section('title', 'Add Task')

{{-- Back button in the header --}}
@section('header-action')
    <a href="{{ route('tasks.index') }}" class="btn btn-ghost">
        ← Back to List
    </a>
@endsection

@section('content')

{{-- Centred glass card form container (max-width 560px via .form-card CSS) --}}
<div class="glass-card form-card">

    {{-- Page heading with gradient text --}}
    <div class="form-page-title">Add New Task</div>
    <p class="form-page-sub">Fill in the details below to create a task.</p>

    {{--
        HTML FORM
        action="{{ route('tasks.store') }}"  → submits to POST /tasks
        method="POST"                        → HTTP verb

        @csrf generates a hidden security token input:
        <input type="hidden" name="_token" value="...">
        Laravel requires this on every state-changing form to prevent
        Cross-Site Request Forgery (CSRF) attacks.
    --}}
    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf

        {{-- ─── TASK NAME ─────────────────────────────────────────── --}}
        <div class="form-group">
            <label class="form-label" for="name">Task Name</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                placeholder="e.g. Logo Design"
                value="{{ old('name') }}"
                {{-- old('name') re-populates the field if validation failed --}}
                autofocus
                {{-- autofocus places the cursor here when the page loads --}}
            />
            {{--
                @error('name') ... @enderror
                This block is only rendered if the 'name' field failed validation.
                $message contains the specific error text, e.g. "The name field is required."
            --}}
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- ─── PRIORITY DROPDOWN ──────────────────────────────────── --}}
        <div class="form-group">
            <label class="form-label" for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control">
                <option value="" disabled {{ old('priority') ? '' : 'selected' }}>
                    Select priority…
                </option>
                {{--
                    Loop over Task::PRIORITIES constant: ['High', 'Medium', 'Low']
                    For each value, create an <option>.
                    old('priority') === $p → re-selects the same option after a validation error.
                --}}
                @foreach(\App\Models\Task::PRIORITIES as $p)
                    <option value="{{ $p }}" {{ old('priority') === $p ? 'selected' : '' }}>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
            @error('priority')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- ─── DEADLINE DATE PICKER ───────────────────────────────── --}}
        <div class="form-group">
            <label class="form-label" for="deadline">Deadline</label>
            <input
                type="date"
                id="deadline"
                name="deadline"
                class="form-control"
                value="{{ old('deadline') }}"
            />
            @error('deadline')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- ─── STATUS DROPDOWN ────────────────────────────────────── --}}
        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="" disabled {{ old('status') ? '' : 'selected' }}>
                    Select status…
                </option>
                {{--
                    Loop over Task::STATUSES constant:
                    ['to_do' => 'To Do', 'in_progress' => 'In Progress', ...]
                    $value = the DB value ('to_do'),  $label = the display text ('To Do')
                --}}
                @foreach(\App\Models\Task::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ old('status') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- ─── ACTION BUTTONS ─────────────────────────────────────── --}}
        <div class="form-actions">
            {{-- Submit button — triggers form POST --}}
            <button type="submit" class="btn btn-primary">
                ✅ Create Task
            </button>
            {{-- Cancel — go back without saving --}}
            <a href="{{ route('tasks.index') }}" class="btn btn-ghost">
                Cancel
            </a>
        </div>

    </form>

</div>

@endsection