<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password',
        'phone', 'document_id', 'birth_date', 'address', 'profile_image','location',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function entrepreneur()
    {
        return $this->hasOne(Entrepreneur::class);
    }
    public function isAdmin(): bool
{
    return $this->hasRole('super-admin');
}

public function isEntrepreneur(): bool
{
    return $this->hasRole('emprendedor');
}

public function isClient(): bool
{
    return $this->hasRole('cliente');
}


}
