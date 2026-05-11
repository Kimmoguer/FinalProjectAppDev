<?php

/*
 * =============================================================================
 * FILE: tests/Feature/TaskCrudTest.php
 * =============================================================================
 *
 * PURPOSE:
 *   This file contains AUTOMATED TESTS for the entire CRUD workflow.
 *   Running these tests proves that every feature works correctly —
 *   without you having to manually click through the app.
 *
 * WHAT ARE AUTOMATED TESTS?
 *   Instead of: "Open browser → click Add Task → fill form → check DB"
 *   You run:    php artisan test
 *   Laravel simulates HTTP requests and checks the responses automatically.
 *
 * TYPES OF TESTS HERE:
 *   READ tests    → Does the page load? Do the right tasks appear?
 *   CREATE tests  → Does the form save correctly? Does validation catch bad input?
 *   UPDATE tests  → Does editing a task save the new values?
 *   DELETE tests  → Is the task removed from the database?
 *   ORM tests     → Do Eloquent scopes and accessors work correctly?
 *
 * HOW TO RUN:
 *   php artisan test                         → run all tests
 *   php artisan test --filter=TaskCrudTest   → run only this file
 *
 * EXPECTED OUTPUT (all passing):
 *   PASS  Tests\Feature\TaskCrudTest
 *   ✓ it displays the task index page          0.12s
 *   ✓ it lists all tasks on index              0.08s
 *   ... (18 tests total)
 * =============================================================================
 */

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    /*
     * RefreshDatabase trait resets the database before each test.
     * This ensures tests are isolated — one test can't pollute another.
     *
     * Because we use SQLite, this creates a fresh in-memory DB each time.
     * It runs all migrations automatically before each test.
     */
    use RefreshDatabase;

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Returns a valid task data array for use in tests.
     *
     * Using a helper avoids repeating the same array in every test.
     * The $overrides parameter lets individual tests customise specific fields.
     *
     * EXAMPLE:
     *   $this->validPayload()
     *     → ['name' => 'Test Task', 'priority' => 'High', ...]
     *
     *   $this->validPayload(['name' => 'My Custom Task', 'status' => 'completed'])
     *     → ['name' => 'My Custom Task', 'priority' => 'High', ...]
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'     => 'Test Task',
            'priority' => 'High',
            'deadline' => '2026-12-31',
            'status'   => 'to_do',
        ], $overrides);
    }

    // =========================================================================
    // READ TESTS — Does the page load and show data correctly?
    // =========================================================================

    /** @test */
    public function it_displays_the_task_index_page(): void
    {
        /*
         * $this->get() simulates a browser visiting GET /tasks.
         * assertStatus(200) checks the HTTP response is "OK".
         * assertViewIs() checks the correct Blade template was used.
         */
        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
    }

    /** @test */
    public function it_lists_all_tasks_on_index(): void
    {
        /*
         * Task::create() uses Eloquent ORM to insert rows directly.
         * We don't go through the HTTP form here — just direct DB writes.
         */
        Task::create($this->validPayload(['name' => 'Alpha Task']));
        Task::create($this->validPayload(['name' => 'Beta Task', 'status' => 'in_progress']));

        $response = $this->get(route('tasks.index'));

        /*
         * assertSee() checks that the text appears somewhere in the HTML response.
         * Both tasks should appear since we're on the "all" tab (no filter).
         */
        $response->assertSee('Alpha Task');
        $response->assertSee('Beta Task');
    }

    /** @test */
    public function it_filters_tasks_by_status(): void
    {
        Task::create($this->validPayload(['name' => 'Todo Task',    'status' => 'to_do']));
        Task::create($this->validPayload(['name' => 'Working Task', 'status' => 'in_progress']));

        // Visit with ?status=to_do — should only show the to_do task
        $response = $this->get(route('tasks.index', ['status' => 'to_do']));

        $response->assertSee('Todo Task');
        /*
         * assertDontSee() verifies that text does NOT appear.
         * 'Working Task' has status 'in_progress', so it should be filtered out.
         */
        $response->assertDontSee('Working Task');
    }

    // =========================================================================
    // CREATE TESTS — Does the form save new tasks correctly?
    // =========================================================================

    /** @test */
    public function it_shows_the_create_task_form(): void
    {
        $response = $this->get(route('tasks.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.create');
    }

    /** @test */
    public function it_stores_a_new_task_in_the_database(): void
    {
        $payload = $this->validPayload(['name' => 'My New Task']);

        // Simulate submitting the Add Task form
        $response = $this->post(route('tasks.store'), $payload);

        // Should redirect back to the task list (not show an error)
        $response->assertRedirect(route('tasks.index'));

        /*
         * assertDatabaseHas() queries the actual SQLite database
         * and verifies a row with these values exists.
         * This proves Eloquent ORM actually saved the data.
         */
        $this->assertDatabaseHas('tasks', [
            'name'     => 'My New Task',
            'priority' => 'High',
            'deadline' => '2026-12-31',
            'status'   => 'to_do',
        ]);
    }

    /** @test */
    public function it_fails_validation_when_name_is_missing(): void
    {
        // Submit with an empty name — should FAIL validation
        $response = $this->post(route('tasks.store'), $this->validPayload(['name' => '']));

        /*
         * assertSessionHasErrors('name') checks that the session contains
         * a validation error for the 'name' field.
         * This means the controller's $request->validate() caught the empty name.
         */
        $response->assertSessionHasErrors('name');

        // The database should still be empty — nothing was saved
        $this->assertDatabaseCount('tasks', 0);
    }

    /** @test */
    public function it_fails_validation_when_priority_is_invalid(): void
    {
        // 'Ultra' is not in Task::PRIORITIES → should fail Rule::in() validation
        $response = $this->post(
            route('tasks.store'),
            $this->validPayload(['priority' => 'Ultra'])
        );

        $response->assertSessionHasErrors('priority');
    }

    /** @test */
    public function it_fails_validation_when_deadline_is_not_a_date(): void
    {
        // 'not-a-date' can't be parsed as a date → should fail 'date' validation rule
        $response = $this->post(
            route('tasks.store'),
            $this->validPayload(['deadline' => 'not-a-date'])
        );

        $response->assertSessionHasErrors('deadline');
    }

    /** @test */
    public function it_fails_validation_when_status_is_invalid(): void
    {
        // 'flying' is not in Task::STATUSES → should fail Rule::in() validation
        $response = $this->post(
            route('tasks.store'),
            $this->validPayload(['status' => 'flying'])
        );

        $response->assertSessionHasErrors('status');
    }

    // =========================================================================
    // UPDATE TESTS — Does editing a task save changes?
    // =========================================================================

    /** @test */
    public function it_shows_the_edit_form_for_an_existing_task(): void
    {
        // First, create a task to edit
        $task = Task::create($this->validPayload());

        $response = $this->get(route('tasks.edit', $task));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.edit');
        // The form should show the task's current name
        $response->assertSee($task->name);
    }

    /** @test */
    public function it_updates_a_task_in_the_database(): void
    {
        $task = Task::create($this->validPayload(['name' => 'Old Name']));

        // Simulate submitting the Edit form with new values
        $response = $this->put(
            route('tasks.update', $task),
            $this->validPayload(['name' => 'New Name', 'status' => 'in_progress'])
        );

        // Should redirect (not error)
        $response->assertRedirect();

        /*
         * assertDatabaseHas() verifies the row was actually updated in the DB.
         * We check both the new name AND that it's the same row (same id).
         */
        $this->assertDatabaseHas('tasks', [
            'id'     => $task->id,
            'name'   => 'New Name',
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function it_fails_update_validation_when_name_is_missing(): void
    {
        $task = Task::create($this->validPayload());

        $response = $this->put(
            route('tasks.update', $task),
            $this->validPayload(['name' => ''])
        );

        $response->assertSessionHasErrors('name');
    }

    // =========================================================================
    // DELETE TESTS — Is the task removed from the database?
    // =========================================================================

    /** @test */
    public function it_deletes_a_task_from_the_database(): void
    {
        $task = Task::create($this->validPayload(['name' => 'Deletable Task']));

        // Simulate clicking the "Remove" button (DELETE request)
        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));

        /*
         * assertDatabaseMissing() confirms the row is GONE from the DB.
         * This verifies $task->delete() (Eloquent ORM) actually ran the DELETE SQL.
         */
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_task(): void
    {
        // Try to delete a task with ID 9999 — doesn't exist
        // Route-model binding should auto-return 404
        $response = $this->delete(route('tasks.destroy', 9999));

        $response->assertStatus(404);
    }

    // =========================================================================
    // ELOQUENT ORM DIRECT TESTS — Are the Model methods working correctly?
    // =========================================================================

    /** @test */
    public function it_creates_a_task_via_eloquent_orm(): void
    {
        // Call Eloquent directly (bypassing HTTP)
        $task = Task::create($this->validPayload());

        // $task should be a Task instance with an auto-generated id
        $this->assertInstanceOf(Task::class, $task);
        $this->assertNotNull($task->id);
        $this->assertEquals('Test Task', $task->name);
    }

    /** @test */
    public function it_reads_a_task_via_eloquent_orm(): void
    {
        $created = Task::create($this->validPayload(['name' => 'Readable']));

        // Task::find() fires: SELECT * FROM tasks WHERE id = ? LIMIT 1
        $found = Task::find($created->id);

        $this->assertNotNull($found);
        $this->assertEquals('Readable', $found->name);
    }

    /** @test */
    public function it_scopes_tasks_by_status_via_eloquent(): void
    {
        // Create 3 tasks: 2 to_do, 1 completed
        Task::create($this->validPayload(['status' => 'to_do']));
        Task::create($this->validPayload(['status' => 'to_do']));
        Task::create($this->validPayload(['status' => 'completed']));

        // The byStatus scope should only return the 2 'to_do' tasks
        $todoTasks = Task::byStatus('to_do')->get();

        $this->assertCount(2, $todoTasks);
        // Verify EVERY returned task has status 'to_do'
        $todoTasks->each(fn ($t) => $this->assertEquals('to_do', $t->status));
    }

    /** @test */
    public function it_returns_human_readable_status_label(): void
    {
        $task = Task::create($this->validPayload(['status' => 'in_progress']));

        // Test the getStatusLabelAttribute accessor
        $this->assertEquals('In Progress', $task->statusLabel);
    }

    /** @test */
    public function it_returns_correct_priority_css_class(): void
    {
        $high   = Task::create($this->validPayload(['priority' => 'High']));
        $medium = Task::create($this->validPayload(['priority' => 'Medium']));
        $low    = Task::create($this->validPayload(['priority' => 'Low']));

        // Test the getPriorityClassAttribute accessor for all three levels
        $this->assertEquals('priority-high',   $high->priorityClass);
        $this->assertEquals('priority-medium', $medium->priorityClass);
        $this->assertEquals('priority-low',    $low->priorityClass);
    }

    /** @test */
    public function it_casts_deadline_to_a_carbon_date(): void
    {
        $task = Task::create($this->validPayload(['deadline' => '2026-06-15']));

        /*
         * Because of protected $casts = ['deadline' => 'date'] in Task.php,
         * accessing $task->deadline should return a Carbon\Carbon instance,
         * not a raw string.
         */
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->deadline);
        $this->assertEquals('2026-06-15', $task->deadline->format('Y-m-d'));
    }
}