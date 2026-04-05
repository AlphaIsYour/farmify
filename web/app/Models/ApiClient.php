<?php
// app/Models/ApiClient.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiClient extends Model
{
    protected $fillable = ['name', 'api_key', 'is_active'];

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }
}

