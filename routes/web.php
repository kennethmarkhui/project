<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->withTrashed()->name('users.edit');
    Route::patch('/users/{user}/update', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{id}/bulk-update', [UserController::class, 'bulkUpdate'])->name('users.bulk_update');
    Route::delete('/users/{user}/delete', [UserController::class, 'destroy'])->name('users.destroy');
    Route::delete('/users/{id}/bulk-delete', [UserController::class, 'bulkDestroy'])->name('users.bulk_destroy');
    Route::patch('/users/{user}/restore', [UserController::class, 'restore'])->withTrashed()->name('users.restore');
    Route::patch('/users/{id}/bulk-restore', [UserController::class, 'bulkRestore'])->withTrashed()->name('users.bulk_restore');
    Route::delete('/users/{user}/force-delete', [UserController::class, 'forceDelete'])->withTrashed()->name('users.force_delete');
    Route::delete('/users/{id}/bulk-force-delete', [UserController::class, 'bulkForceDelete'])->withTrashed()->name('users.bulk_force_delete');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles/create', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::patch('/roles/{role}/update', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}/delete', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions');
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
