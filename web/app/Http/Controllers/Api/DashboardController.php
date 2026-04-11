<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Device;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function data(): JsonResponse
    {
        $devices = Device::with(['latestSensor', 'pumpStatus'])->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'device_code' => $d->device_code,
                'zone_name'   => $d->zone_name,
                'status'      => $d->status,
                'last_seen'   => $d->last_seen,
                'pump_status' => $d->pumpStatus?->status ?? 'idle',
                'latest_sensor' => $d->latestSensor,
            ]);

        return response()->json($devices);
    }

    public function log(): JsonResponse
    {
        $logs = ActivityLog::with('device')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json($logs);
    }
}