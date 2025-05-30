<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldCity extends Model
{
    protected $table = 'old_cities';
    protected $guarded = ['id'];


    public function categories()
    {
        return $this->hasMany(OldCategory::class, 'city_id');
    }
    
    public function institutes()
    {
        return $this->hasMany(OldInstitute::class, 'city_id');
    }
    
    public function news()
    {
        return $this->hasMany(OldNews::class, 'city_id');
    }
    
    public function notifications()
    {
        return $this->belongsToMany(OldNotification::class, 'old_notification_cities', 'city_id', 'notification_id');
    }
    
    public function contacts()
    {
        return $this->hasMany(OldContact::class, 'city_id');
    }
    
    public function contactus()
    {
        return $this->hasMany(OldContactus::class, 'city_id');
    }
    
    public function config()
    {
        return $this->hasOne(OldCityConfig::class, 'city_id');
    }

    public function admins()
    {
        return $this->belongsToMany(OldAdmin::class, 'old_admin_cities', 'city_id', 'admin_id');
    }
}
