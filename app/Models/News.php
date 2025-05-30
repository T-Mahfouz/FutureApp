<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [];

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function images()
    {
        return $this->belongsToMany(Media::class, 'news_images', 'news_id', 'image_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
