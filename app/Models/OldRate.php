<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldRate extends Model
{
    protected $table = 'old_rates';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(OldUser::class, 'user_id');
    }
    
    public function institute()
    {
        return $this->belongsTo(OldInstitute::class, 'institute_id');
    }
}
