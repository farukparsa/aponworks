<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemoryController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('memories', [MemoryController::class, 'store'])
        ->name('memories.store');

    Route::patch('memories/{memory}/complete', [MemoryController::class, 'complete'])
        ->name('memories.complete');

    Route::patch('memories/{memory}/reopen', [MemoryController::class, 'reopen'])
        ->name('memories.reopen');

    Route::delete('memories/{memory}', [MemoryController::class, 'destroy'])
        ->name('memories.destroy');
});

require __DIR__.'/settings.php';