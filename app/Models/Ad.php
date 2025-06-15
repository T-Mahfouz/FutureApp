<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['expiration_date' => 'datetime'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
