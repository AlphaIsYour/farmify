<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorData extends Model
{
    public $timestamps = false;
    protected $table = 'sensor_data';
    protected $fillable = ['device_id', 'soil_moisture', 'temperature', 'humidity', 'recorded_at'];
    protected $casts    = ['recorded_at' => 'datetime'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
