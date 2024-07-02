<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('tasks', TaskController::class);
    Route::get('/plans', [PlanController::class, 'index'])->name('plan.index');
    Route::get('/plans/{plan}', [PlanController::class, 'update'])->name('plan.update');
    Route::delete('/plans', [PlanController::class, 'destroy'])->name('plan.destroy');
});

require __DIR__ . '/auth.php';
