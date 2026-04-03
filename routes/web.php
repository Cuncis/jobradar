<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Livewire\JobSearch;

// Homepage + search — all handled by the Livewire component
Route::get('/', JobSearch::class)->name('home');

// Single job detail (still a regular controller — no Livewire needed here)
Route::get('/jobs/{id}', [JobController::class, 'show'])
    ->where('id', '[0-9]+')
    ->name('jobs.show');