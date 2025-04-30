<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\Permission\PermissionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;

// Halaman Utama
Route::get('/', function () {
    return view('welcome');
});

// Dashboard (semua role masuk dashboard umum dulu)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'role:superadmin,admin,operator,user'])->name('dashboard');

// Admin Panel
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Logout Admin
    Route::post('/logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])->name('logout');

    // Manajemen Permission
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
        Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
    });

    // Manajemen Role
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\Role\RoleController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\Role\RoleController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\Role\RoleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\Role\RoleController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\Role\RoleController::class, 'destroy'])->name('destroy');
    });


    // Nanti disini lanjut route admin lain (Role, User, Settings, dll)
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route khusus admin testing
Route::get('/admin-only', function () {
    return 'Admin Area';
})->middleware(['auth', 'role:admin']);

// Auth Routes Breeze
require __DIR__ . '/auth.php';
