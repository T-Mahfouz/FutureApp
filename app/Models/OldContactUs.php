<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldContactUs extends Model
{
    protected $table = 'old_contactus';
    protected $guarded = ['id'];

    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
}
