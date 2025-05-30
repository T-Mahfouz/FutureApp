<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldNews extends Model
{
    protected $table = 'old_news';
    protected $guarded = ['id'];

    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
    
    public function images()
    {
        return $this->hasMany(OldNewsImage::class, 'news_id');
    }
    
    public function notifications()
    {
        return $this->hasMany(OldNotification::class, 'news_id');
    }
}
