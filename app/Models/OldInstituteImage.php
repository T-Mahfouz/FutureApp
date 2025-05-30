<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldInstituteImage extends Model
{
    protected $table = 'old_institute_images';
    protected $guarded = ['id'];

    public function institute()
    {
        return $this->belongsTo(OldInstitute::class, 'institute_id');
    }
}
