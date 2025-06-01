<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCity extends Model
{
    protected $guarded = [];

    public function Notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function City()
    {
        return $this->belongsTo(City::class);
    }
}
