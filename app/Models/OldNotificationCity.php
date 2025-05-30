<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldNotificationCity extends Model
{
    protected $table = 'old_notification_cities';
    protected $guarded = ['id'];

    public function notification()
    {
        return $this->belongsTo(OldNotification::class, 'notification_id');
    }
    
    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
}
