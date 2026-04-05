<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\SettingsController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::redirect('/', '/dashboard');

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/',        [DashboardController::class, 'index'])    ->name('index');
    Route::get('/devices', [DashboardController::class, 'devices'])  ->name('devices');
    Route::post('/devices',[DashboardController::class, 'storeDevice'])->name('devices.store');
    Route::get('/commands',[DashboardController::class, 'commands']) ->name('commands');
    Route::get('/logs',    [DashboardController::class, 'logs'])     ->name('logs');

    // AJAX endpoints
    Route::post('/command',      [DashboardController::class, 'sendCommand'])    ->name('command.send');
    Route::get('/chart-data',    [DashboardController::class, 'chartData'])      ->name('chart.data');
    Route::get('/api/devices',   [DashboardController::class, 'deviceStatuses'])->name('api.devices');

     // Settings
    Route::get('/settings',                       [SettingsController::class, 'index'])         ->name('settings');
    Route::post('/settings/threshold',            [SettingsController::class, 'saveThreshold']) ->name('settings.threshold');
    Route::post('/settings/worker',               [SettingsController::class, 'saveWorker'])    ->name('settings.worker');
    Route::post('/settings/dashboard',            [SettingsController::class, 'saveDashboard']) ->name('settings.dashboard');
    Route::post('/settings/keys',                 [SettingsController::class, 'storeKey'])      ->name('settings.key.store');
    Route::patch('/settings/keys/{client}',       [SettingsController::class, 'toggleKey'])     ->name('settings.key.toggle');
    Route::patch('/settings/keys/{client}/regen', [SettingsController::class, 'regenerateKey'])->name('settings.key.regenerate');
    Route::delete('/settings/keys/{client}',      [SettingsController::class, 'deleteKey'])     ->name('settings.key.delete');
    Route::delete('/settings/clear/sensor',       [SettingsController::class, 'clearSensor'])   ->name('settings.clear.sensor');
    Route::delete('/settings/clear/log',          [SettingsController::class, 'clearLog'])      ->name('settings.clear.log');
});