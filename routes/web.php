<?php

use App\Http\Controllers\MemoryController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::post('memories', [MemoryController::class, 'store'])
        ->name('memories.store');
});

require __DIR__.'/settings.php';