<?php
// ══════════════════════════════════════════════════════════════
// app/Http/Controllers/Api/IngestController.php
// ══════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Device;
use App\Models\PumpStatus;
use App\Models\SensorData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngestController extends Controller
{
    // POST /api/ingest
    // Body: { device_code, soil_moisture, temperature?, humidity? }
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_code'   => 'required|string',
            'soil_moisture' => 'required|numeric|min:0|max:100',
            'temperature'   => 'nullable|numeric',
            'humidity'      => 'nullable|numeric',
        ]);

        $client = $request->get('_api_client');

        $device = Device::firstOrCreate(
            ['device_code' => $data['device_code']],
            [
                'api_client_id' => $client->id,
                'zone_name'     => $data['device_code'],
                'status'        => 'online',
            ]
        );

        $device->update(['status' => 'online', 'last_seen' => now()]);

        $sensor = SensorData::create([
            'device_id'     => $device->id,
            'soil_moisture' => $data['soil_moisture'],
            'temperature'   => $data['temperature'] ?? null,
            'humidity'      => $data['humidity'] ?? null,
        ]);

        // Pastikan pump_status row tersedia
        PumpStatus::firstOrCreate(['device_id' => $device->id]);

        // Auto-command: jika kelembapan < 30% → kirim start_pump otomatis
        $this->checkAutoIrigation($device, $data['soil_moisture'], $client);

        return response()->json(['message' => 'Data ingested', 'id' => $sensor->id], 201);
    }

    // GET /api/devices
    public function index(): JsonResponse
    {
        $devices = Device::with(['latestSensor', 'pumpStatus'])->get();
        return response()->json($devices);
    }

    private function checkAutoIrigation(Device $device, float $moisture, $client): void
    {
        $threshold = 30.0;

        if ($moisture < $threshold) {
            $pending = $device->commands()
                ->where('status', 'pending')
                ->where('command_type', 'start_pump')
                ->exists();

            if (!$pending) {
                $cmd = $device->commands()->create([
                    'api_client_id' => $client->id,
                    'command_type'  => 'start_pump',
                    'status'        => 'pending',
                    'source'        => 'auto',
                ]);

                ActivityLog::create([
                    'device_id'   => $device->id,
                    'command_id'  => $cmd->id,
                    'action'      => 'AUTO_COMMAND',
                    'description' => "Kelembapan {$moisture}% di bawah threshold. Perintah irigasi otomatis dibuat.",
                ]);
            }
        }
    }
}



