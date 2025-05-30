<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldAdminCity extends Model
{
    protected $table = 'old_admin_cities';
    protected $guarded = ['id'];

    public function admin()
    {
        return $this->belongsTo(OldAdmin::class, 'admin_id');
    }
    
    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
}
