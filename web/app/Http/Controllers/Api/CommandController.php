<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Command;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id'    => 'required|exists:devices,id',
            'command_type' => 'required|in:start_pump,stop_pump',
        ]);

        $client = $request->get('_api_client');

        $cmd = Command::create([
            'device_id'    => $data['device_id'],
            'api_client_id'=> $client->id,
            'command_type' => $data['command_type'],
            'status'       => 'pending',
            'source'       => 'manual',
        ]);

        ActivityLog::create([
            'device_id'   => $data['device_id'],
            'command_id'  => $cmd->id,
            'action'      => 'COMMAND_SENT',
            'description' => "Perintah manual [{$data['command_type']}] dikirim.",
        ]);

        return response()->json(['message' => 'Command queued', 'command_id' => $cmd->id], 201);
    }

    // GET /api/command/pending
    // Digunakan oleh worker untuk polling
    public function pending(): JsonResponse
    {
        $commands = Command::with('device')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        return response()->json($commands);
    }

    // PATCH /api/command/{id}/done
    // Body: { status: done|failed }
    public function done(Request $request, int $id): JsonResponse
    {
        $cmd = Command::findOrFail($id);
        $data = $request->validate(['status' => 'required|in:done,failed']);

        $cmd->update(['status' => $data['status'], 'executed_at' => now()]);

        return response()->json(['message' => 'Command updated']);
    }
}