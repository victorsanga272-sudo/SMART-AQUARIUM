<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aquarium extends Model
{
    protected $table = 'aquariums';

    protected $fillable = [
        'vivo_user_id',
        'public_id',
        'sain',
        'device_token_hash',
    ];

    protected $hidden = [
        'device_token_hash',
        'public_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Aquarium $aquarium): void {
            if ($aquarium->sain) {
                return;
            }

            do {
                $sain = '';
                for ($digit = 0; $digit < 20; $digit++) {
                    $sain .= (string) random_int(0, 9);
                }
            } while (static::query()->where('sain', $sain)->exists());

            $aquarium->sain = $sain;
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(VivoUsers::class, 'vivo_user_id');
    }

    public function telemetry(): HasMany
    {
        return $this->hasMany(AquariumTelemetry::class);
    }

    public function actuatorCommands(): HasMany
    {
        return $this->hasMany(AquariumActuatorCommand::class);
    }
}