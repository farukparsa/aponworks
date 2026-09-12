<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return inertia('Welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated APONWORKS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | General View
    |--------------------------------------------------------------------------
    */

    Route::get('/memories', [MemoryController::class, 'index'])
        ->name('memories.index');

    /*
    |--------------------------------------------------------------------------
    | Dedicated Task / Note / Diary Pages
    |--------------------------------------------------------------------------
    */

    Route::get('/tasks', [MemoryController::class, 'tasks'])
        ->name('tasks.index');

    Route::get('/notes', [MemoryController::class, 'notes'])
        ->name('notes.index');

    Route::get('/diary', [MemoryController::class, 'diary'])
        ->name('diary.index');

    Route::get('/tasks/completed', [MemoryController::class, 'completedTasks'])
        ->name('tasks.completed');

    /*
    |--------------------------------------------------------------------------
    | BIN
    |--------------------------------------------------------------------------
    */

    Route::get('/bin/tasks', [MemoryController::class, 'deletedTasks'])
        ->name('bin.tasks');

    Route::get('/bin/notes', [MemoryController::class, 'deletedNotes'])
        ->name('bin.notes');

    Route::get('/bin/diary', [MemoryController::class, 'deletedDiary'])
        ->name('bin.diary');

    /*
    |--------------------------------------------------------------------------
    | Create / Edit Memory
    |--------------------------------------------------------------------------
    */

    Route::post('/memories', [MemoryController::class, 'store'])
        ->name('memories.store');

    Route::patch('/memories/{memory}', [MemoryController::class, 'update'])
        ->name('memories.update');

    /*
    |--------------------------------------------------------------------------
    | Task Actions
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/memories/{memory}/complete',
        [MemoryController::class, 'complete']
    )->name('memories.complete');

    Route::patch(
        '/memories/{memory}/reopen',
        [MemoryController::class, 'reopen']
    )->name('memories.reopen');

    /*
    |--------------------------------------------------------------------------
    | Move to BIN
    |--------------------------------------------------------------------------
    */

    Route::delete('/memories/{memory}', [MemoryController::class, 'destroy'])
        ->name('memories.destroy');

    /*
    |--------------------------------------------------------------------------
    | Deleted Item Detail / Restore / Permanent Delete
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/memories/deleted/{memory}',
        [MemoryController::class, 'showDeleted']
    )->name('memories.deleted.show');

    Route::patch(
        '/memories/{memory}/restore',
        [MemoryController::class, 'restore']
    )->name('memories.restore');

    Route::delete(
        '/memories/{memory}/permanent',
        [MemoryController::class, 'forceDestroy']
    )->name('memories.force-destroy');
});

require __DIR__.'/settings.php';