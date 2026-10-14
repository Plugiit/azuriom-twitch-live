<?php

use Azuriom\Plugin\PlugiitTwitchLive\Controllers\ChannelController;
use Azuriom\Plugin\PlugiitTwitchLive\Controllers\TwitchLiveController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [TwitchLiveController::class, 'index'])->name('index');

Route::middleware(['auth', 'throttle:10,1'])->group(function () {
    Route::post('/channel', [ChannelController::class, 'store'])->name('channel.store');
    Route::delete('/channel', [ChannelController::class, 'destroy'])->name('channel.destroy');
});
