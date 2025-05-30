<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'city_id',
        'image_id',
        'name',
        'brief_description',
        'description',
        'lat',
        'lon',
        'website',
        'youtube',
        'facebook',
        'instagram',
        'telegram',
        'video_link',
        'valid',
        'arrangement_order',
    ];

    protected $attributes = [
        'valid' => 1,
        'arrangement_order' => 1,
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'service_categories');
    }

    public function images()
    {
        return $this->belongsToMany(Media::class, 'service_images', 'service_id', 'image_id');
    }

    public function phones()
    {
        return $this->hasMany(ServicePhone::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function averageRating()
    {
        return $this->rates()->avg('rate');
    }
}
