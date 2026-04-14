<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Palette extends Model
{
    protected $fillable = ['user_id', 'name', 'colors', 'source'];

    protected $casts = [
        'colors' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}