<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriveController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BackupController;

// Site root: signed-in users go to the dashboard, everyone else to the login page.
Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('/dashboard', [DashboardController::class, 'index']);
    // Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])
        ->name('dashboard.stats');
    
    // Drive - Main file management
    Route::prefix('drive')->name('drive.')->group(function () {
        Route::get('/', [DriveController::class, 'index'])->name('index');
        Route::get('/search', [DriveController::class, 'search'])->name('search');
        Route::get('/folder/{folder}', [DriveController::class, 'index'])->name('folder');
        
        Route::post('/folder/create', [DriveController::class, 'createFolder'])->name('folder.create');
        Route::put('/folder/{folder}', [DriveController::class, 'updateFolder'])->name('folder.update');
        Route::delete('/folder/{folder}', [DriveController::class, 'deleteFolder'])->name('folder.delete');
        
        Route::post('/upload', [DriveController::class, 'uploadFiles'])->name('files.upload');
        Route::post('/upload-folder', [DriveController::class, 'uploadFolder'])->name('folder.upload');
        Route::get('/download/{file}', [DriveController::class, 'downloadFile'])->name('files.download');
        Route::delete('/file/{file}', [DriveController::class, 'deleteFile'])->name('files.delete');
        Route::get('/thumbnail/{file}', [DriveController::class, 'thumbnail'])->name('files.thumbnail');
        Route::get('/preview/{file}', [DriveController::class, 'preview'])->name('files.preview');
        Route::get('/stream/{file}', [DriveController::class, 'stream'])->name('files.stream');
        
        Route::get('/archived', [DriveController::class, 'archived'])->name('archived');
        Route::post('/archive/{folder}', [DriveController::class, 'archiveFolder'])->name('archive');
        Route::post('/unarchive/{folder}', [DriveController::class, 'unarchiveFolder'])->name('unarchive');
    });
    
    // User Management
    Route::middleware(['can:manage-users'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/restore', [UserManagementController::class, 'restore'])->withTrashed()->name('restore');
    });

    // Backup & Sync
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::get('/synced', [BackupController::class, 'synced'])->name('synced');
        Route::post('/sync/{file}', [BackupController::class, 'sync'])->name('sync');
        Route::post('/sync-all', [BackupController::class, 'syncAll'])->name('sync-all');
    });
    
    // Activity Logs - Define these routes explicitly
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::delete('/activity-logs/{activity}', [ActivityLogController::class, 'destroy'])->name('activity-logs.destroy');
    Route::post('/activity-logs/clear-all', [ActivityLogController::class, 'clearAll'])->name('activity-logs.clear-all');
    
   // Settings (Admin only)
    Route::middleware(['role:administrator'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/toggle-maintenance', [SettingsController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
        Route::post('/backup-database', [SettingsController::class, 'backupDatabase'])->name('backup-database');
    });
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
