<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ApiClient;
use App\Models\Command;
use App\Models\Device;
use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    private function getSettings(): array
    {
        return config('farming');
    }

    public function index()
    {
        $settings   = $this->getSettings();
        $apiClients = ApiClient::with('devices')->latest()->get();
        $sysInfo    = [
            'total_devices'  => Device::count(),
            'total_records'  => SensorData::count(),
            'total_commands' => Command::count(),
            'total_logs'     => ActivityLog::count(),
        ];

        return view('dashboard.settings', compact('settings', 'apiClients', 'sysInfo'));
    }

    public function saveThreshold(Request $request)
    {
        $request->validate([
            'threshold_moisture' => 'required|integer|min:10|max:60',
            'threshold_stop'     => 'required|integer|min:40|max:100',
        ]);
        $this->writeEnv([
            'THRESHOLD_MOISTURE' => $request->threshold_moisture,
            'THRESHOLD_STOP'     => $request->threshold_stop,
        ]);
        return back()->with('success', 'Irrigation threshold saved.');
    }

    public function saveWorker(Request $request)
    {
        $request->validate([
            'poll_interval'   => 'required|integer|min:1|max:60',
            'device_timeout'  => 'required|integer|min:10|max:600',
            'ingest_interval' => 'required|integer|min:5|max:300',
        ]);
        $this->writeEnv([
            'POLL_INTERVAL'   => $request->poll_interval,
            'DEVICE_TIMEOUT'  => $request->device_timeout,
            'INGEST_INTERVAL' => $request->ingest_interval,
            'AUTO_IRRIGATION' => $request->boolean('auto_irrigation') ? 'true' : 'false',
        ]);
        return back()->with('success', 'Worker configuration saved.');
    }

    public function saveDashboard(Request $request)
    {
        $this->writeEnv([
            'AUTO_REFRESH'        => $request->boolean('auto_refresh') ? 'true' : 'false',
            'SHOW_OFFLINE'        => $request->boolean('show_offline') ? 'true' : 'false',
            'DEFAULT_CHART_RANGE' => $request->input('default_chart_range', '6h'),
        ]);
        return back()->with('success', 'Dashboard preferences saved.');
    }

    public function storeKey(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $key = Str::random(48);
        ApiClient::create(['name' => $request->name, 'api_key' => $key, 'is_active' => 1]);
        return back()->with('success', "API key generated: {$key}");
    }

    public function toggleKey(ApiClient $client)
    {
        $client->update(['is_active' => !$client->is_active]);
        return back()->with('success', 'API key status updated.');
    }

    public function regenerateKey(ApiClient $client)
    {
        $key = Str::random(48);
        $client->update(['api_key' => $key]);
        return back()->with('success', "Key regenerated: {$key}");
    }

    public function deleteKey(ApiClient $client)
    {
        $client->delete();
        return back()->with('success', 'API key deleted.');
    }

    public function clearSensor()
    {
        SensorData::truncate();
        return back()->with('success', 'All sensor data cleared.');
    }

    public function clearLog()
    {
        ActivityLog::truncate();
        return back()->with('success', 'Activity log cleared.');
    }

    private function writeEnv(array $data): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);
        foreach ($data as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }
        file_put_contents($envPath, $content);
    }
}