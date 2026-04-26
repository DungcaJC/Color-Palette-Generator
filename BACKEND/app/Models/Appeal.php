<?php
// BACKEND/app/Models/Appeal.php
// Appeal model representing user appeals against warnings, with relationships to warnings, users, and appeal images.
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
    protected $fillable = ['warning_id', 'user_id', 'apology_text', 'status', 'admin_response', 'reviewed_by', 'reviewed_at'];
    protected $casts = ['reviewed_at' => 'datetime'];

    public function warning() { return $this->belongsTo(Warning::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function images() { return $this->hasMany(AppealImage::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}