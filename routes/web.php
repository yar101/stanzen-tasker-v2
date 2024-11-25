<?php

use App\Http\Controllers\ContractorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    redirect('/dashboard');
//    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
//    return Inertia::render('Dashboard', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
//})->middleware('auth');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::resource('/contractors', ContractorController::class)->middleware('auth');

Route::resource('/tasks', TaskController::class)->middleware('auth');
Route::patch('/task/{task}/update-status', [TaskController::class, 'updateStatus'])->middleware('auth')->name('tasks.updateStatus');
Route::patch('/task/{task}/update-deadline', [TaskController::class, 'updateDeadline'])->middleware('auth')->name('tasks.updateDeadline');

