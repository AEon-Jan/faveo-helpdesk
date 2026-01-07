<?php

use App\Plugins\ActiveDirectoryAuth\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'install', 'roles', 'auth', 'update'])->group(function () {
    Route::get('active-directory-auth/settings', [SettingsController::class, 'index'])
        ->name('active-directory-auth.settings');
});
