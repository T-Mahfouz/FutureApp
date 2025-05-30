<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminCity extends Model
{
    protected $guarded = ['id'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
