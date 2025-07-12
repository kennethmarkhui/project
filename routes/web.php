<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('users', [UserController::class, 'index'])->name('users');
    Route::get('/users/edit/{user}', [UserController::class, 'edit'])->withTrashed()->name('users.edit');
    Route::patch('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->withTrashed()->name('users.destroy');
    Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->withTrashed()->name('users.restore');
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
