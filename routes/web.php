<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;

// Homepage — search form
Route::get('/', [JobController::class, 'index'])->name('home');

// Search results
// Important: this must come BEFORE /jobs/{id}
// otherwise Laravel would try to match "search" as an {id}
Route::get('/jobs/search', [JobController::class, 'search'])->name('jobs.search');

// Single job detail
Route::get('/jobs/{id}', [JobController::class, 'show'])
    ->where('id', '[0-9]+') // only match numeric IDs
    ->name('jobs.show');

// Clear old cache (POST so it can't be triggered by just visiting a URL)
Route::post('/jobs/cache/clear', [JobController::class, 'clearCache'])
    ->name('jobs.cache.clear');