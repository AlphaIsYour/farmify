<?php
// routes/api.php

use App\Http\Controllers\Api\IngestController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

// Semua route dilindungi middleware API key
Route::middleware('auth.apikey')->group(function () {

    // ── Device / Sensor ─────────────────────────────────────
    Route::post('/ingest',         [IngestController::class, 'store']);   // Device kirim data sensor
    Route::get('/devices',         [IngestController::class, 'index']);   // List semua device

    // ── Command System ───────────────────────────────────────
    Route::post('/command/send',   [CommandController::class, 'send']);   // Dashboard kirim perintah
    Route::get('/command/pending', [CommandController::class, 'pending']); // Worker ambil command pending
    Route::patch('/command/{id}/done', [CommandController::class, 'done']); // Worker update selesai

    // ── Status ───────────────────────────────────────────────
    Route::post('/status/update',  [StatusController::class, 'update']);  // Worker update status pompa

    // ── Dashboard ────────────────────────────────────────────
    Route::get('/dashboard/data',  [DashboardController::class, 'data']); // Data sensor semua device
    Route::get('/dashboard/log',   [DashboardController::class, 'log']);  // Log aktivitas
});