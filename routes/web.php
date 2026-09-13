<?php

use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDomainController;
use App\Http\Controllers\Admin\AdminSubdomainController;
use App\Http\Controllers\Admin\AdminServerController;
use App\Http\Controllers\Admin\AdminGithubSshController;
use App\Http\Controllers\Admin\AdminServerTaskController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Bulk delete routes (before resource routes)
        Route::delete('user/bulk-delete', [AdminUserController::class, 'bulkDelete'])->name('user.bulk-delete');
        Route::patch('user/bulk-status', [AdminUserController::class, 'bulkStatus'])->name('user.bulk-status');
        Route::delete('role/bulk-delete', [AdminRoleController::class, 'bulkDelete'])->name('role.bulk-delete');
        Route::delete('server/bulk-delete', [AdminServerController::class, 'bulkDelete'])->name('server.bulk-delete');
        Route::patch('server/bulk-status', [AdminServerController::class, 'bulkStatus'])->name('server.bulk-status');
        Route::delete('domain/bulk-delete', [AdminDomainController::class, 'bulkDelete'])->name('domain.bulk-delete');
        Route::patch('domain/bulk-status', [AdminDomainController::class, 'bulkStatus'])->name('domain.bulk-status');
        Route::delete('subdomain/bulk-delete', [AdminSubdomainController::class, 'bulkDelete'])->name('subdomain.bulk-delete');
        Route::patch('subdomain/bulk-status', [AdminSubdomainController::class, 'bulkStatus'])->name('subdomain.bulk-status');
        Route::delete('github-ssh/bulk-delete', [AdminGithubSshController::class, 'bulkDelete'])->name('github-ssh.bulk-delete');
        Route::patch('github-ssh/bulk-status', [AdminGithubSshController::class, 'bulkStatus'])->name('github-ssh.bulk-status');

        Route::resource('user', AdminUserController::class);
        Route::patch('user/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('user.toggle-status');

        Route::resource('role', AdminRoleController::class);
        
        Route::resource('server', AdminServerController::class);
        Route::patch('server/{id}/toggle-status', [AdminServerController::class, 'toggleStatus'])->name('server.toggle-status');
        Route::post('server/{id}/test-connection', [AdminServerController::class, 'testConnection'])->name('server.test-connection');
        Route::post('server/{id}/reload-nginx', [AdminServerController::class, 'reloadNginx'])->name('server.reload-nginx');
        Route::post('server/{id}/restart-nginx', [AdminServerController::class, 'restartNginx'])->name('server.restart-nginx');

        Route::resource('domain', AdminDomainController::class);
        Route::patch('domain/{id}/toggle-status', [AdminDomainController::class, 'toggleStatus'])->name('domain.toggle-status');
        Route::get('domain/{id}/preview-config', [AdminDomainController::class, 'previewConfig'])->name('domain.preview-config');
        Route::post('domain/{id}/publish-config', [AdminDomainController::class, 'publishConfig'])->name('domain.publish-config');
        Route::post('domain/{id}/unpublish', [AdminDomainController::class, 'unpublish'])->name('domain.unpublish');

        Route::resource('subdomain', AdminSubdomainController::class);
        Route::patch('subdomain/{id}/toggle-status', [AdminSubdomainController::class, 'toggleStatus'])->name('subdomain.toggle-status');
        Route::get('subdomain/{id}/preview-config', [AdminSubdomainController::class, 'previewConfig'])->name('subdomain.preview-config');
        Route::post('subdomain/{id}/publish-config', [AdminSubdomainController::class, 'publishConfig'])->name('subdomain.publish-config');
        Route::post('subdomain/{id}/unpublish', [AdminSubdomainController::class, 'unpublish'])->name('subdomain.unpublish');

        Route::resource('github-ssh', AdminGithubSshController::class);
        Route::patch('github-ssh/{id}/toggle-status', [AdminGithubSshController::class, 'toggleStatus'])->name('github-ssh.toggle-status');

        Route::get('server-task', [AdminServerTaskController::class, 'index'])->name('server-task.index');
        Route::post('server-task/execute', [AdminServerTaskController::class, 'execute'])->name('server-task.execute');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
