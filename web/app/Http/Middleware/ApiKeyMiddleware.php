<?php
// app/Http/Middleware/ApiKeyMiddleware.php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');

        if (!$key) {
            return response()->json(['error' => 'API key missing'], 401);
        }

        $client = ApiClient::where('api_key', $key)
                           ->where('is_active', 1)
                           ->first();

        if (!$client) {
            return response()->json(['error' => 'Invalid or inactive API key'], 403);
        }

        // Inject client ke request agar bisa dipakai controller
        $request->merge(['_api_client' => $client]);

        return $next($request);
    }
}

// ─────────────────────────────────────────────────────────────
// Daftarkan di bootstrap/app.php:
// ->withMiddleware(function (Middleware $middleware) {
//     $middleware->alias(['auth.apikey' => ApiKeyMiddleware::class]);
// })