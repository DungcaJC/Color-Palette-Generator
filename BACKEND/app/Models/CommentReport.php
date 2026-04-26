<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CommentReport extends Model
{
    protected $fillable = ['comment_id', 'reporter_id', 'topic', 'details'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}