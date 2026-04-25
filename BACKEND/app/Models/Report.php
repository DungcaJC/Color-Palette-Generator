<?php
// app/Models/Report.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['post_id', 'reporter_id', 'topic', 'details', 'status'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}