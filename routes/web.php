<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\BeratLinenController;
use App\Http\Controllers\StokChemicalController;
use App\Http\Controllers\PemakaianChemicalController;
use App\Http\Controllers\PenerimaanChemicalController;
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
    Route::resource('stok-chemical', StokChemicalController::class)->except(['show']);
    Route::get('/pemakaian-chemical', [PemakaianChemicalController::class, 'index'])->name('pemakaian-chemical.index');
    Route::get('/pemakaian-chemical/catat', [PemakaianChemicalController::class, 'create'])->name('pemakaian-chemical.create');
    Route::post('/pemakaian-chemical', [PemakaianChemicalController::class, 'store'])->name('pemakaian-chemical.store');

    Route::get('/penerimaan-chemical', [PenerimaanChemicalController::class, 'index'])->name('penerimaan-chemical.index');
    Route::get('/penerimaan-chemical/tambah', [PenerimaanChemicalController::class, 'create'])->name('penerimaan-chemical.create');
    Route::post('/penerimaan-chemical', [PenerimaanChemicalController::class, 'store'])->name('penerimaan-chemical.store');

});

require __DIR__.'/auth.php';
