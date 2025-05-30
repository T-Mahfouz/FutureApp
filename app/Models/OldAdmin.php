<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldAdmin extends Model
{
    protected $table = 'old_admins';
    protected $guarded = ['id'];

    public function cities()
    {
        return $this->belongsToMany(OldCity::class, 'old_admin_cities', 'admin_id', 'city_id');
    }
}
