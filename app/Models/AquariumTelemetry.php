<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AquariumTelemetry extends Model
{
    protected $table = 'aquarium_telemetry';

    protected $fillable = [
        'temperature',
        'ph',
        'turbidity',
        'water_level',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'ph' => 'float',
            'turbidity' => 'float',
            'water_level' => 'float',
        ];
    }

    public function aquarium(): BelongsTo
    {
        return $this->belongsTo(Aquarium::class);
    }
}