<?php

use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanPhotoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Warga\LaporanController as WargaLaporanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', function (Request $request) {
    return redirect()->to($request->user()->dashboardRoute());
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->prefix('notifikasi')->name('notifications.')->group(function (): void {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::patch('/baca-semua', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::patch('/{notificationId}/baca', [NotificationController::class, 'markAsRead'])->name('read');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.status');
        Route::get('/laporans', [AdminLaporanController::class, 'index'])->name('laporans.index');
        Route::get('/laporans/{laporan}', [AdminLaporanController::class, 'show'])->name('laporans.show');
        Route::patch('/laporans/{laporan}/status', [AdminLaporanController::class, 'updateStatus'])->name('laporans.status');
    });

Route::middleware(['auth', 'role:warga'])
    ->prefix('warga')
    ->name('warga.')
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'warga'])->name('dashboard');
        Route::resource('laporans', WargaLaporanController::class);
    });

Route::get('/laporans/{laporan}/foto', [LaporanPhotoController::class, 'show'])
    ->middleware('auth')
    ->name('laporans.foto');
