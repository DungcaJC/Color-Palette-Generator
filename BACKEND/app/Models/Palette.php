<?php

// app/Models/Palette.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Palette extends Model
{
    protected $fillable = [
        'name',
        'colors',
        'source',
        'user_id',
    ];

    protected $casts = [
        'colors' => 'array', // ✅ Auto cast JSON column to/from PHP array
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}