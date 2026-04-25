<?php
// app/Models/Post.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'image', 'caption', 'colors', 'category', 'likes_count'];

    protected $casts = ['colors' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}