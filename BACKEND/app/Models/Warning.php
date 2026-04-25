<?php
// app/Models/Warning.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Warning extends Model
{
    protected $fillable = ['user_id','admin_id','post_id','report_category','auto_caption','admin_text','expires_days','expires_at','status'];
    protected $casts = ['expires_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
    public function post() { return $this->belongsTo(Post::class); }
}