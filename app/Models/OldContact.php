<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldContact extends Model
{
    protected $table = 'old_contacts';
    protected $guarded = ['id'];

    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
}
