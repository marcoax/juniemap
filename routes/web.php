<?php

use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LocationController::class, 'index'])->name('home');

// Public API endpoints for locations
Route::prefix('locations')->group(function (): void {
    Route::get('/', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/search', [LocationController::class, 'search'])->middleware('throttle:60,1')->name('locations.search');
    Route::get('/{id}', [LocationController::class, 'show'])->middleware('throttle:60,1')->name('locations.show');
    Route::get('/{id}/details', [LocationController::class, 'details'])->middleware('throttle:60,1')->name('locations.details');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return \Inertia\Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
