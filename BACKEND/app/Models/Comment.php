<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['post_id', 'user_id', 'parent_id', 'content', 'likes_count'];

    public function user() { return $this->belongsTo(User::class); }
    public function post() { return $this->belongsTo(Post::class); }
    public function replies() { return $this->hasMany(Comment::class, 'parent_id')->with('user:id,name,avatar,role')->withCount(['likes as liked_by_user' => fn($q) => $q->where('user_id', auth()->id() ?? 0)])->latest(); }
    public function likes() { return $this->hasMany(CommentLike::class); }
}
