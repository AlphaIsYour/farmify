<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Command extends Model
{
    public $timestamps  = false;
    protected $table = 'commands';
    protected $fillable = ['device_id', 'api_client_id', 'command_type', 'status', 'source', 'created_at', 'executed_at'];
    protected $casts    = ['created_at' => 'datetime', 'executed_at' => 'datetime'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}