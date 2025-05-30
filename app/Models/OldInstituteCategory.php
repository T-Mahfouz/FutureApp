<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldInstituteCategory extends Model
{
    protected $table = 'old_institute_categories';
    protected $guarded = ['id'];

    public function institute()
    {
        return $this->belongsTo(OldInstitute::class, 'institute_id');
    }
    
    public function category()
    {
        return $this->belongsTo(OldCategory::class, 'category_id');
    }
}
