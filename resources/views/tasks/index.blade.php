{{--
================================================================================
FILE: resources/views/tasks/index.blade.php
================================================================================

PURPOSE:
  This is the MAIN TASK LIST page. It shows all tasks in a table,
  with tab navigation to filter by status.

VARIABLES AVAILABLE (passed from TaskController@index):
  $tasks   → a Collection of Task model objects (the filtered list)
  $status  → the active filter string (e.g. 'to_do') or null for "all"
  $counts  → array of count per tab: ['all'=>5, 'to_do'=>2, ...]

BLADE DIRECTIVES USED:
  @extends  → inherits the layout from layouts/app.blade.php
  @section  → fills a @yield slot in the layout
  @foreach  → loops over a collection
  @if/@else → conditional display
  {{ }}     → outputs a value (auto-escaped to prevent XSS attacks)
  {!! !!}   → outputs raw HTML (we don't use this for user input — XSS risk)
================================================================================
--}}

@extends('layouts.app')  {{-- Use the master layout template --}}

@section('title', 'My Tasks')  {{-- Sets the browser tab title --}}

{{--
    Inject the "Add Task" button into the header's right side.
    route('tasks.create') generates the URL for the create form.
--}}
@section('header-action')
    <a href="{{ route('tasks.create') }}" class="btn btn-primary">
        ＋ Add Task
    </a>
@endsection

@section('content')

    {{-- =====================================================================
         STATUS FILTER TABS
         Each tab is a link that adds ?status=... to the URL.
         When clicked, it reloads the page and TaskController@index
         filters the query using the byStatus() scope.
    ===================================================================== --}}
    <nav class="tab-bar" role="tablist">

        {{--
            "Home" tab — shows ALL tasks (no status filter).
            The 'active' CSS class is added when $status is null.
            is_null($status) checks whether we're on the "all" view.
        --}}
        <a href="{{ route('tasks.index') }}"
           class="tab {{ is_null($status) ? 'active' : '' }}"
           role="tab">
            🏠 Home
            <span class="tab-badge">{{ $counts['all'] }}</span>
        </a>

        {{-- "To Do" tab — filters for status = 'to_do' --}}
        <a href="{{ route('tasks.index', ['status' => 'to_do']) }}"
           class="tab {{ $status === 'to_do' ? 'active' : '' }}"
           role="tab">
            📋 To Do
            <span class="tab-badge">{{ $counts['to_do'] }}</span>
        </a>

        {{-- "In Progress" tab --}}
        <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}"
           class="tab {{ $status === 'in_progress' ? 'active' : '' }}"
           role="tab">
            ⚙️ In Progress
            <span class="tab-badge">{{ $counts['in_progress'] }}</span>
        </a>

        {{-- "Completed" tab --}}
        <a href="{{ route('tasks.index', ['status' => 'completed']) }}"
           class="tab {{ $status === 'completed' ? 'active' : '' }}"
           role="tab">
            ✅ Completed
            <span class="tab-badge">{{ $counts['completed'] }}</span>
        </a>

        {{-- "Submitted" tab --}}
        <a href="{{ route('tasks.index', ['status' => 'submitted']) }}"
           class="tab {{ $status === 'submitted' ? 'active' : '' }}"
           role="tab">
            📤 Submitted
            <span class="tab-badge">{{ $counts['submitted'] }}</span>
        </a>

    </nav>

    {{-- =====================================================================
         GLASS CARD — wraps the task table
    ===================================================================== --}}
    <div class="glass-card">

        {{-- Card header: shows the current view name and task count --}}
        <div class="section-header">
            <div>
                <div class="section-title">
                    @if(is_null($status))
                        All Tasks
                    @else
                        {{--
                            Look up the human-readable label from the STATUSES constant.
                            e.g. 'in_progress' → 'In Progress'
                        --}}
                        {{ \App\Models\Task::STATUSES[$status] }}
                    @endif
                </div>
                {{-- Pluralise "task" correctly: "1 task" vs "5 tasks" --}}
                <div class="section-count">
                    {{ $tasks->count() }} task{{ $tasks->count() !== 1 ? 's' : '' }}
                </div>
            </div>
        </div>

        {{-- =====================================================================
             EMPTY STATE — shown when no tasks match the current filter
        ===================================================================== --}}
        @if($tasks->isEmpty())

            <div class="empty-state">
                <div class="empty-icon">🗂️</div>
                <div class="empty-title">No tasks here yet</div>
                <div class="empty-sub">
                    @if(is_null($status))
                        Click <strong>Add Task</strong> to create your first task.
                    @else
                        No tasks with status <strong>{{ \App\Models\Task::STATUSES[$status] }}</strong>.
                    @endif
                </div>
            </div>

        {{-- =====================================================================
             TASK TABLE — shown when tasks exist
        ===================================================================== --}}
        @else

            <div style="overflow-x:auto; padding: 0 .25rem 1.25rem;">
                <table class="task-table">

                    {{-- Column headers --}}
                    <thead>
                        <tr>
                            <th class="task-sn">S.N.</th>
                            <th>Task Name</th>
                            <th>Priority</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        {{--
                            @foreach loops over the $tasks Collection.
                            Each $task is a fully loaded Task model object.
                            $index is the loop counter (0-based), so we add +1 for display.

                            Available properties on each $task:
                              $task->id           → database ID
                              $task->name         → task name
                              $task->priority     → 'High', 'Medium', or 'Low'
                              $task->deadline     → Carbon date object (from $casts)
                              $task->status       → raw status value
                              $task->statusLabel  → accessor: human-readable status
                              $task->priorityClass → accessor: CSS class name
                        --}}
                        @foreach($tasks as $index => $task)
                        <tr>

                            {{-- Serial number (1-based) --}}
                            <td class="task-sn">{{ $index + 1 }}.</td>

                            {{-- Task name --}}
                            <td class="task-name">{{ $task->name }}</td>

                            {{--
                                Priority badge.
                                $task->priorityClass returns 'priority-high', 'priority-medium',
                                or 'priority-low' — matching the CSS classes defined in the layout.
                            --}}
                            <td>
                                <span class="badge {{ $task->priorityClass }}">
                                    {{ $task->priority }}
                                </span>
                            </td>

                            {{--
                                Deadline formatted as YYYY-MM-DD.
                                $task->deadline is a Carbon object (because of $casts),
                                so we call ->format() on it.
                            --}}
                            <td class="task-deadline">
                                {{ $task->deadline->format('Y-m-d') }}
                            </td>

                            {{--
                                Status badge.
                                $task->statusLabel returns the display text (e.g. "In Progress").
                            --}}
                            <td>
                                <span class="badge" style="
                                    background: rgba(124,58,237,.15);
                                    color: #a78bfa;
                                    border: 1px solid rgba(124,58,237,.3);
                                ">
                                    {{ $task->statusLabel }}
                                </span>
                            </td>

                            {{-- Action buttons: Update and Remove --}}
                            <td>
                                <div class="task-actions">

                                    {{--
                                        UPDATE button — links to the edit form.
                                        route('tasks.edit', $task) generates:
                                        /tasks/3/edit  (where 3 is $task->id)
                                    --}}
                                    <a href="{{ route('tasks.edit', $task) }}"
                                       class="btn btn-success btn-sm">
                                        ✏️ Update
                                    </a>

                                    {{--
                                        REMOVE button — submits a DELETE request.

                                        WHY WE NEED A FORM:
                                        HTML links only make GET requests.
                                        To DELETE a resource we need a form with method POST
                                        and the @method('DELETE') Blade directive.

                                        @method('DELETE') adds a hidden input:
                                        <input type="hidden" name="_method" value="DELETE">
                                        Laravel reads this and routes it to destroy().

                                        @csrf adds the CSRF token input for security.
                                        Without it, Laravel rejects the request.

                                        onsubmit="return confirm(...)" shows a browser
                                        confirmation popup before deleting.
                                    --}}
                                    <form action="{{ route('tasks.destroy', $task) }}"
                                          method="POST"
                                          onsubmit="return confirm('Remove task \'{{ addslashes($task->name) }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            🗑️ Remove
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        @endif {{-- end isEmpty / else --}}

    </div>{{-- /glass-card --}}

@endsection