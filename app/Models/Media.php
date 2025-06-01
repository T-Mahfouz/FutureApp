<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    protected $attributes = [
        'type' => 'image',
    ];

    public function admins()
    {
        return $this->hasMany(Admin::class, 'image_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'image_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'image_id');
    }

    public function serviceImages()
    {
        return $this->hasMany(ServiceImage::class, 'image_id');
    }

    public function news()
    {
        return $this->hasMany(News::class, 'image_id');
    }

    public function newsImages()
    {
        return $this->hasMany(NewsImage::class, 'image_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'image_id');
    }
}
