<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldCityConfig extends Model
{
    protected $table = 'old_city_configs';
    protected $guarded = ['id'];

    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
}
