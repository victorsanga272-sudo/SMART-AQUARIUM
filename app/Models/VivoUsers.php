<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class VivoUsers extends Authenticatable
{
    use Notifiable;

    protected $table = 'vivo_users';

    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::created(function (VivoUsers $user): void {
            $user->aquarium()->create([
                'public_id' => (string) Str::uuid(),
            ]);
        });
    }

    public function aquarium(): HasOne
    {
        return $this->hasOne(Aquarium::class, 'vivo_user_id');
    }
}
