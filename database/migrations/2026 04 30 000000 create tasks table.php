<?php

/**
 * ============================================================
 *  FILE: database/migrations/2026_04_30_000000_create_tasks_table.php
 * ============================================================
 *  This is a DATABASE MIGRATION.
 *
 *  Migrations are version-controlled database schemas.
 *  Instead of writing raw SQL to create tables, Laravel lets
 *  you describe the table structure in PHP, then run:
 *
 *    php artisan migrate   -- runs the up() method (creates table)
 *    php artisan migrate:rollback -- runs down() (drops table)
 *
 *  This migration creates the `tasks` table that the Task
 *  Eloquent model maps to.
 * ============================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * UP:  Run when you execute `php artisan migrate`
     *
     * Schema::create() builds the CREATE TABLE SQL statement.
     * Blueprint $table gives you helper methods for each column type.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {

            // Auto-incrementing primary key: id INTEGER PRIMARY KEY
            $table->id();

            // Task name -- a short text string (VARCHAR 255)
            $table->string('name');

            // Priority level -- restricted to 3 allowed values
            // enum() generates: priority ENUM('High','Medium','Low')
            $table->enum('priority', ['High', 'Medium', 'Low'])->default('Medium');

            // Deadline -- stored as a DATE column (YYYY-MM-DD)
            $table->date('deadline');

            // Workflow status -- restricted to the 4 tab values
            $table->enum('status', ['to_do', 'in_progress', 'completed', 'submitted'])
                  ->default('to_do');

            // Adds created_at and updated_at TIMESTAMP columns
            // Laravel updates these automatically on create/update
            $table->timestamps();
        });
    }

    /**
     * DOWN:  Run when you rollback the migration.
     * Drops the entire `tasks` table (reverses up()).
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};