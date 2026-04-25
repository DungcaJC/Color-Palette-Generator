<?php
// app/Models/SavedPost.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedPost extends Model
{
    protected $fillable = ['post_id', 'user_id'];
}