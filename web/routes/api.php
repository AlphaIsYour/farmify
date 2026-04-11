<?php
use App\Http\Controllers\Api\IngestController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.apikey')->group(function () {

    // ── Device / Sensor 
    Route::post('/ingest',         [IngestController::class, 'store']);
    Route::get('/devices',         [IngestController::class, 'index']);

    // ── Command System
    Route::post('/command/send',   [CommandController::class, 'send']);   
    Route::get('/command/pending', [CommandController::class, 'pending']); 
    Route::patch('/command/{id}/done', [CommandController::class, 'done']); 

    // ── Status
    Route::post('/status/update',  [StatusController::class, 'update']); 

    // ── Dashboard 
    Route::get('/dashboard/data',  [DashboardController::class, 'data']);
    Route::get('/dashboard/log',   [DashboardController::class, 'log']); 
});