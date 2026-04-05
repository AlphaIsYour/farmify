<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    protected $table = 'devices';
    protected $fillable = ['api_client_id', 'device_code', 'zone_name', 'location', 'status', 'last_seen'];

    protected $casts = ['last_seen' => 'datetime'];

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class);
    }

    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorData::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }

    public function pumpStatus(): HasOne
    {
        return $this->hasOne(PumpStatus::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function latestSensor(): HasOne
    {
        return $this->hasOne(SensorData::class)->latestOfMany('recorded_at');
    }
}
