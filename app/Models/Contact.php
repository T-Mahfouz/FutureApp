<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $guarded = ['id'];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
