<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps  = false;
    protected $table = 'activity_log';
    protected $fillable = ['device_id', 'command_id', 'action', 'description'];
    protected $casts    = ['created_at' => 'datetime'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function command(): BelongsTo
    {
        return $this->belongsTo(Command::class);
    }
}