<?php

use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDomainController;
use App\Http\Controllers\Admin\AdminSubdomainController;
use App\Http\Controllers\Admin\AdminServerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::resource('user', AdminUserController::class);
        Route::resource('role', AdminRoleController::class);
        
        Route::resource('server', AdminServerController::class);
        Route::patch('server/{id}/toggle-status', [AdminServerController::class, 'toggleStatus'])->name('server.toggle-status');
        Route::post('server/{id}/test-connection', [AdminServerController::class, 'testConnection'])->name('server.test-connection');

        Route::resource('domain', AdminDomainController::class);
        Route::patch('domain/{id}/toggle-status', [AdminDomainController::class, 'toggleStatus'])->name('domain.toggle-status');
        Route::get('domain/{id}/preview-config', [AdminDomainController::class, 'previewConfig'])->name('domain.preview-config');
        Route::post('domain/{id}/publish-config', [AdminDomainController::class, 'publishConfig'])->name('domain.publish-config');

        Route::resource('subdomain', AdminSubdomainController::class);
        Route::patch('subdomain/{id}/toggle-status', [AdminSubdomainController::class, 'toggleStatus'])->name('subdomain.toggle-status');
        Route::get('subdomain/{id}/preview-config', [AdminSubdomainController::class, 'previewConfig'])->name('subdomain.preview-config');
        Route::post('subdomain/{id}/publish-config', [AdminSubdomainController::class, 'publishConfig'])->name('subdomain.publish-config');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
