<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PumpStatus extends Model
{
    public $timestamps  = false;
    protected $table = 'pump_status';
    protected $fillable = ['device_id', 'status'];
    protected $casts    = ['updated_at' => 'datetime'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}