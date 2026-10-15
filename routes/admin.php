<?php

use Azuriom\Plugin\TwitchLive\Controllers\Admin\SettingController;
use Azuriom\Plugin\TwitchLive\Controllers\Admin\StreamerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware('can:twitch-live.admin')->group(function () {
    Route::get('/settings', [SettingController::class, 'show'])->name('settings');
    Route::post('/settings', [SettingController::class, 'save'])->name('settings.save');
    Route::post('/refresh', [SettingController::class, 'refresh'])->name('refresh');

    Route::post('/streamers/{streamer}/approve', [StreamerController::class, 'approve'])->name('streamers.approve');
    Route::post('/streamers/{streamer}/reject', [StreamerController::class, 'reject'])->name('streamers.reject');
    Route::resource('streamers', StreamerController::class)->except('show');
});
