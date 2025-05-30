<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldNotification extends Model
{
    protected $table = 'old_notifications';
    protected $guarded = ['id'];

    public function institute()
    {
        return $this->belongsTo(OldInstitute::class, 'institute_id');
    }
    
    public function news()
    {
        return $this->belongsTo(OldNews::class, 'news_id');
    }
    
    public function cities()
    {
        return $this->belongsToMany(OldCity::class, 'old_notification_cities', 'notification_id', 'city_id');
    }
}
