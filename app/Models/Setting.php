<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'key',
        'value',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
