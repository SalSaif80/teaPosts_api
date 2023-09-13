<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeaPost extends Model
{
    use HasFactory;
    protected $table = "tea_posts";
    protected $fillable = ["tea_name","tea_image_path","tea_type","how_to_prepare_tea","tea_in_water_time","user_id","recommended_wznh"];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments() : HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
    


}
