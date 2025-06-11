<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = [];

    public function getPivotIDAttribute()
    {
        return $this->pivot->id ?? null;
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function images()
    {
        return $this->belongsToMany(Media::class, 'news_images', 'news_id', 'image_id')->withPivot('id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
