<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function subServices()
    {
        return $this->hasMany(Service::class, 'parent_id');
    }

    public function parentService()
    {
        return $this->belongsTo(Service::class, 'parent_id');
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

    public function allMedia()
    {
        return $this->hasMany(ServiceImage::class, 'service_id');
    }
}
