<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AquariumActuatorCommand extends Model
{
    protected $table = 'aquarium_actuator_commands';

    protected $fillable = [
        'command',
        'status',
        'result',
        'dispatched_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'dispatched_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function aquarium(): BelongsTo
    {
        return $this->belongsTo(Aquarium::class);
    }
}