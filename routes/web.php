<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\BeratLinenController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/checklist/tambah', [ChecklistController::class, 'create'])->name('checklist.create');
    Route::post('/checklist', [ChecklistController::class, 'store'])->name('checklist.store');
    Route::resource('berat-linen', BeratLinenController::class)->except(['show']);
    Route::resource('checklist/berat', BeratLinenController::class)
           ->parameters(['berat' => 'beratLinen'])
           ->names('berat-linen')
            ->except(['show']);

});

require __DIR__.'/auth.php';
