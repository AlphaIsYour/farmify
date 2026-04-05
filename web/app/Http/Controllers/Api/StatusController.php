<?php
// ══════════════════════════════════════════════════════════════
// app/Http/Controllers/Api/StatusController.php
// ══════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PumpStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    // POST /api/status/update
    // Body: { device_id, status, command_id? }
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id'  => 'required|exists:devices,id',
            'status'     => 'required|in:idle,running,stopped',
            'command_id' => 'nullable|exists:commands,id',
        ]);

        PumpStatus::updateOrCreate(
            ['device_id' => $data['device_id']],
            ['status'    => $data['status']]
        );

        ActivityLog::create([
            'device_id'   => $data['device_id'],
            'command_id'  => $data['command_id'] ?? null,
            'action'      => 'PUMP_STATUS_UPDATE',
            'description' => "Status pompa diperbarui menjadi [{$data['status']}].",
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}