<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldUserPhone extends Model
{
    protected $table = 'old_user_phones';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(OldUser::class, 'user_id');
    }
}
