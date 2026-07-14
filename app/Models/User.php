<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'isActive',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'isActive' => 'boolean',
        ];
    }

    // Relationships
    public function responseSessions(): HasMany
    {
        return $this->hasMany(ResponseSession::class);
    }

    // Scopes
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeRespondent($query)
    {
        return $query->where('role', 'respondent');
    }

    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }
}
