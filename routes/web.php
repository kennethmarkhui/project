<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('users', [UserController::class, 'index'])->middleware(['auth', 'verified'])->name('users');
Route::get('/users/edit/{user}', [UserController::class, 'edit'])->middleware(['auth', 'verified'])->name('users.edit');
Route::patch('/users/edit/{user}', [UserController::class, 'update'])->middleware(['auth', 'verified'])->name('users.update');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
