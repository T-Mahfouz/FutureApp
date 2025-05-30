<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldAbout extends Model
{
    protected $table = 'old_abouts';
    protected $guarded = ['id'];
    
    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
}
