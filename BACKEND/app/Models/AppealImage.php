<?php
// BACKEND/app/Models/AppealImage.php
// AppealImage model representing images attached to user appeals, with a relationship to the Appeal model.
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AppealImage extends Model
{
    protected $fillable = ['appeal_id', 'image'];
}