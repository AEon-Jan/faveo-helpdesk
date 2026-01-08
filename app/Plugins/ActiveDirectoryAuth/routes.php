<?php

use App\Plugins\ActiveDirectoryAuth\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'install', 'roles', 'auth', 'update'])->group(function () {
    Route::get('active-directory-auth/settings', [SettingsController::class, 'index'])
        ->name('active-directory-auth.settings');
    Route::post('active-directory-auth/settings', [SettingsController::class, 'update'])
        ->name('active-directory-auth.settings.update');
    Route::post('active-directory-auth/test-connection', [SettingsController::class, 'testConnection'])
        ->name('active-directory-auth.test-connection');
    Route::post('active-directory-auth/import', [SettingsController::class, 'import'])
        ->name('active-directory-auth.import');
});
