<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Command;
use App\Models\Device;
use App\Models\PumpStatus;
use App\Models\SensorData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // ── Overview ─────────────────────────────────────────────
    public function index(): View
    {
        $devices = Device::with(['latestSensor', 'pumpStatus'])->get();

        $avgMoisture = $devices->map(fn($d) => $d->latestSensor?->soil_moisture)->filter()->avg();
        $prevMoisture = SensorData::whereIn('device_id', $devices->pluck('id'))
            ->where('recorded_at', '>=', now()->subHour())
            ->avg('soil_moisture');

        $stats = [
            'devices_online'   => $devices->where('status', 'online')->count(),
            'devices_total'    => $devices->count(),
            'avg_moisture'     => round($avgMoisture ?? 0, 1),
            'moisture_trend'   => round(($avgMoisture ?? 0) - ($prevMoisture ?? 0), 1),
            'active_pumps'     => PumpStatus::where('status', 'running')->count(),
            'commands_today'   => Command::whereDate('created_at', today())->count(),
            'commands_success' => Command::whereDate('created_at', today())->where('status', 'done')->count(),
        ];

        // Moisture trend chart (last 6 hours, every 30 min)
        $chartData = $this->moistureChartData('6h');

        // Commands bar chart (last 7 days)
        $cmdChart = [
            'labels' => collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('d M'))->toArray(),
            'data'   => collect(range(6, 0))->map(fn($i) =>
                Command::whereDate('created_at', now()->subDays($i))->count()
            )->toArray(),
        ];

        // Command distribution doughnut
        $cmdDist = [
            'labels' => ['Done', 'Pending', 'Failed'],
            'data'   => [
                Command::where('status', 'done')->count(),
                Command::whereIn('status', ['pending', 'processing'])->count(),
                Command::where('status', 'failed')->count(),
            ],
        ];

        $recentCommands = Command::with('device')->latest('created_at')->limit(8)->get();
        $recentLogs     = ActivityLog::with('device')->latest('created_at')->limit(8)->get();

        return view('dashboard.index', compact('devices', 'stats', 'chartData', 'cmdChart', 'cmdDist', 'recentCommands', 'recentLogs'));
    }

    // ── Devices ───────────────────────────────────────────────
    public function devices(): View
    {
        $devices = Device::with(['latestSensor', 'pumpStatus'])->paginate(20);
        return view('dashboard.devices', compact('devices'));
    }

    public function storeDevice(Request $request)
    {
        $data = $request->validate([
            'device_code' => 'required|string|unique:devices,device_code',
            'zone_name'   => 'required|string',
            'location'    => 'nullable|string',
        ]);

        // Use first api_client as owner (adjust per your auth logic)
        $client = \App\Models\ApiClient::first();
        $data['api_client_id'] = $client?->id ?? 1;

        Device::create($data);
        PumpStatus::create(['device_id' => Device::latest()->first()->id]);

        return redirect()->route('dashboard.devices')->with('success', 'Device added successfully.');
    }

    // ── Commands ──────────────────────────────────────────────
    public function commands(Request $request): View
    {
        $query = Command::with('device')->latest('created_at');

        if ($request->filled('device_id')) $query->where('device_id', $request->device_id);
        if ($request->filled('type'))      $query->where('command_type', $request->type);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('source'))    $query->where('source', $request->source);

        $commands  = $query->paginate(15);
        $devices   = Device::all();
        $cmdStats  = [
            'done'    => Command::where('status', 'done')->count(),
            'pending' => Command::whereIn('status', ['pending', 'processing'])->count(),
            'failed'  => Command::where('status', 'failed')->count(),
            'auto'    => Command::where('source', 'auto')->count(),
        ];

        return view('dashboard.commands', compact('commands', 'devices', 'cmdStats'));
    }

    // ── Logs ──────────────────────────────────────────────────
    public function logs(Request $request): View
    {
        $query = ActivityLog::with(['device', 'command'])->latest('created_at');

        if ($request->filled('device_id')) $query->where('device_id', $request->device_id);
        if ($request->filled('action'))    $query->where('action', 'like', '%' . $request->action . '%');
        if ($request->filled('date'))      $query->whereDate('created_at', $request->date);

        $logs      = $query->paginate(20);
        $devices   = Device::all();
        $logStats  = [
            'total' => ActivityLog::count(),
            'auto'  => ActivityLog::where('action', 'AUTO_COMMAND')->count(),
            'pump'  => ActivityLog::where('action', 'PUMP_STATUS_UPDATE')->count(),
        ];

        return view('dashboard.logs', compact('logs', 'devices', 'logStats'));
    }

    // ── AJAX: Send command from dashboard ─────────────────────
    public function sendCommand(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id'    => 'required|exists:devices,id',
            'command_type' => 'required|in:start_pump,stop_pump',
        ]);

        $cmd = Command::create([
            'device_id'     => $data['device_id'],
            'api_client_id' => \App\Models\ApiClient::first()?->id ?? 1,
            'command_type'  => $data['command_type'],
            'status'        => 'pending',
            'source'        => 'manual',
        ]);

        ActivityLog::create([
            'device_id'   => $data['device_id'],
            'command_id'  => $cmd->id,
            'action'      => 'COMMAND_SENT',
            'description' => 'Manual command [' . $data['command_type'] . '] sent from dashboard.',
        ]);

        return response()->json(['message' => 'Command queued', 'command_id' => $cmd->id], 201);
    }

    // ── AJAX: Chart data by range ─────────────────────────────
    public function chartData(Request $request): JsonResponse
    {
        return response()->json($this->moistureChartData($request->get('range', '6h')));
    }

    // ── AJAX: Device statuses for polling ─────────────────────
    public function deviceStatuses(): JsonResponse
    {
        return response()->json(
            Device::with(['latestSensor', 'pumpStatus'])
                ->get()
                ->map(fn($d) => [
                    'id'     => $d->id,
                    'status' => $d->status,
                    'pump'   => $d->pumpStatus?->status ?? 'idle',
                    'moisture' => $d->latestSensor?->soil_moisture,
                ])
        );
    }

    // ── Helper: build moisture chart data ─────────────────────
    private function moistureChartData(string $range): array
    {
        [$hours, $step, $format] = match($range) {
            '1h'  => [1,  10,  'H:i'],
            '6h'  => [6,  30,  'H:i'],
            '24h' => [24, 60,  'H:i'],
            '7d'  => [168,360, 'd M'],
            default => [6, 30, 'H:i'],
        };

        $devices  = Device::all();
        $points   = collect(range($hours * 60 / $step, 0))->map(fn($i) => now()->subMinutes($i * $step));
        $labels   = $points->map(fn($t) => $t->format($format))->toArray();

        $datasets = $devices->map(function ($device) use ($points, $step) {
            return [
                'label' => $device->zone_name,
                'data'  => $points->map(function ($t) use ($device, $step) {
                    $row = SensorData::where('device_id', $device->id)
                        ->whereBetween('recorded_at', [$t->copy()->subMinutes($step), $t])
                        ->avg('soil_moisture');
                    return $row ? round($row, 1) : null;
                })->toArray(),
            ];
        })->toArray();

        return compact('labels', 'datasets');
    }
}