<?php

/**
 * ============================================================
 *  FILE: routes/web.php
 * ============================================================
 *  This file maps URLs to Controller methods.
 *
 *  When a browser requests a URL, Laravel looks here first
 *  to decide WHICH controller method should handle it.
 *
 *  Route::resource() generates 7 standard RESTful routes
 *  automatically -- you don't need to write them one by one.
 *
 *  Generated routes for `tasks`:
 *  ┌─────────────────────────────┬─────────┬───────────────────────┐
 *  │ URL                         │ Method  │ Controller Method      │
 *  ├─────────────────────────────┼─────────┼───────────────────────┤
 *  │ /tasks                      │ GET     │ TaskController@index   │
 *  │ /tasks/create               │ GET     │ TaskController@create  │
 *  │ /tasks                      │ POST    │ TaskController@store   │
 *  │ /tasks/{task}/edit          │ GET     │ TaskController@edit    │
 *  │ /tasks/{task}               │ PUT     │ TaskController@update  │
 *  │ /tasks/{task}               │ DELETE  │ TaskController@destroy │
 *  └─────────────────────────────┴─────────┴───────────────────────┘
 *  (show is excluded with ->except(['show']) -- not needed here)
 * ============================================================
 */

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
 * Root URL "/" -> redirect immediately to the task list.
 * This ensures visiting http://127.0.0.1:8000 shows the app.
 */
Route::get('/', fn () => redirect()->route('tasks.index'));

/*
 * Route::resource() registers all 6 RESTful CRUD routes at once.
 *
 * ->except(['show']) removes the GET /tasks/{task} single-task page
 * because our app doesn't need a separate detail page.
 *
 * Named routes generated (use in Blade with route()):
 *   tasks.index   -> route('tasks.index')
 *   tasks.create  -> route('tasks.create')
 *   tasks.store   -> route('tasks.store')
 *   tasks.edit    -> route('tasks.edit', $task)
 *   tasks.update  -> route('tasks.update', $task)
 *   tasks.destroy -> route('tasks.destroy', $task)
 */
Route::resource('tasks', TaskController::class)
    ->except(['show']);