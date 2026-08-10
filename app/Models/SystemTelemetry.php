<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemTelemetry extends Model
{
    protected $table = 'system_telemetry';
    protected $fillable = ['install_id', 'payload', 'recorded_at'];
    protected $casts = ['payload' => 'array', 'recorded_at' => 'datetime'];
}
