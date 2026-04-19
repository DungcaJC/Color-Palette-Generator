<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ✅ Required for token auth

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // ✅ HasApiTokens must be here

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar', // ✅ needed for avatar upload route
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function palettes()
    {
        return $this->hasMany(Palette::class);
    }
}