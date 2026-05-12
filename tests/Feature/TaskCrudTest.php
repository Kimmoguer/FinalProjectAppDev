<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TaskCrudTest
 *
 * Covers every Eloquent ORM operation (Create, Read, Update, Delete)
 * plus form validation and status-filtering logic.
 *
 * Run with:   php artisan test --filter=TaskCrudTest
 */
class TaskCrudTest extends TestCase
{
    use RefreshDatabase;   // Fresh SQLite DB for every test

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /** Return a valid task payload. */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'     => 'Test Task',
            'priority' => 'High',
            'deadline' => '2026-12-31',
            'status'   => 'to_do',
        ], $overrides);
    }

    // ──────────────────────────────────────────────
    // READ TESTS
    // ──────────────────────────────────────────────

    /** @test */
    public function it_displays_the_task_index_page(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
    }

    /** @test */
    public function it_lists_all_tasks_on_index(): void
    {
        Task::create($this->validPayload(['name' => 'Alpha Task']));
        Task::create($this->validPayload(['name' => 'Beta Task', 'status' => 'in_progress']));

        $response = $this->get(route('tasks.index'));

        $response->assertSee('Alpha Task');
        $response->assertSee('Beta Task');
    }

    /** @test */
    public function it_filters_tasks_by_status(): void
    {
        Task::create($this->validPayload(['name' => 'Todo Task',    'status' => 'to_do']));
        Task::create($this->validPayload(['name' => 'Working Task', 'status' => 'in_progress']));

        $response = $this->get(route('tasks.index', ['status' => 'to_do']));

        $response->assertSee('Todo Task');
        $response->assertDontSee('Working Task');
    }

    // ──────────────────────────────────────────────
    // CREATE TESTS
    // ──────────────────────────────────────────────

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

        $response = $this->post(route('tasks.store'), $payload);

        $response->assertRedirect(route('tasks.index'));
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
        $response = $this->post(route('tasks.store'), $this->validPayload(['name' => '']));

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('tasks', 0);
    }

    /** @test */
    public function it_fails_validation_when_priority_is_invalid(): void
    {
        $response = $this->post(
            route('tasks.store'),
            $this->validPayload(['priority' => 'Ultra'])
        );

        $response->assertSessionHasErrors('priority');
    }

    /** @test */
    public function it_fails_validation_when_deadline_is_not_a_date(): void
    {
        $response = $this->post(
            route('tasks.store'),
            $this->validPayload(['deadline' => 'not-a-date'])
        );

        $response->assertSessionHasErrors('deadline');
    }

    /** @test */
    public function it_fails_validation_when_status_is_invalid(): void
    {
        $response = $this->post(
            route('tasks.store'),
            $this->validPayload(['status' => 'flying'])
        );

        $response->assertSessionHasErrors('status');
    }

    // ──────────────────────────────────────────────
    // UPDATE TESTS
    // ──────────────────────────────────────────────

    /** @test */
    public function it_shows_the_edit_form_for_an_existing_task(): void
    {
        $task = Task::create($this->validPayload());

        $response = $this->get(route('tasks.edit', $task));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.edit');
        $response->assertSee($task->name);
    }

    /** @test */
    public function it_updates_a_task_in_the_database(): void
    {
        $task = Task::create($this->validPayload(['name' => 'Old Name']));

        $response = $this->put(
            route('tasks.update', $task),
            $this->validPayload(['name' => 'New Name', 'status' => 'in_progress'])
        );

        $response->assertRedirect();
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

    // ──────────────────────────────────────────────
    // DELETE TESTS
    // ──────────────────────────────────────────────

    /** @test */
    public function it_deletes_a_task_from_the_database(): void
    {
        $task = Task::create($this->validPayload(['name' => 'Deletable Task']));

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_task(): void
    {
        $response = $this->delete(route('tasks.destroy', 9999));

        $response->assertStatus(404);
    }

    // ──────────────────────────────────────────────
    // ELOQUENT ORM UNIT-STYLE TESTS
    // ──────────────────────────────────────────────

    /** @test */
    public function it_creates_a_task_via_eloquent_orm(): void
    {
        $task = Task::create($this->validPayload());

        $this->assertInstanceOf(Task::class, $task);
        $this->assertNotNull($task->id);
        $this->assertEquals('Test Task', $task->name);
    }

    /** @test */
    public function it_reads_a_task_via_eloquent_orm(): void
    {
        $created = Task::create($this->validPayload(['name' => 'Readable']));

        $found = Task::find($created->id);

        $this->assertNotNull($found);
        $this->assertEquals('Readable', $found->name);
    }

    /** @test */
    public function it_scopes_tasks_by_status_via_eloquent(): void
    {
        Task::create($this->validPayload(['status' => 'to_do']));
        Task::create($this->validPayload(['status' => 'to_do']));
        Task::create($this->validPayload(['status' => 'completed']));

        $todoTasks = Task::byStatus('to_do')->get();

        $this->assertCount(2, $todoTasks);
        $todoTasks->each(fn ($t) => $this->assertEquals('to_do', $t->status));
    }

    /** @test */
    public function it_returns_human_readable_status_label(): void
    {
        $task = Task::create($this->validPayload(['status' => 'in_progress']));

        $this->assertEquals('In Progress', $task->statusLabel);
    }

    /** @test */
    public function it_returns_correct_priority_css_class(): void
    {
        $high   = Task::create($this->validPayload(['priority' => 'High']));
        $medium = Task::create($this->validPayload(['priority' => 'Medium']));
        $low    = Task::create($this->validPayload(['priority' => 'Low']));

        $this->assertEquals('priority-high',   $high->priorityClass);
        $this->assertEquals('priority-medium', $medium->priorityClass);
        $this->assertEquals('priority-low',    $low->priorityClass);
    }

    /** @test */
    public function it_casts_deadline_to_a_carbon_date(): void
    {
        $task = Task::create($this->validPayload(['deadline' => '2026-06-15']));

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->deadline);
        $this->assertEquals('2026-06-15', $task->deadline->format('Y-m-d'));
    }
}