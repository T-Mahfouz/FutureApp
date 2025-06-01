<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['id','name','image_id'];

    public function config()
    {
        return $this->hasOne(CityConfig::class);
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_cities');
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'city_id');
    }

    public function contactUs()
    {
        return $this->hasMany(ContactUs::class);
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
