<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldInstitute extends Model
{
    protected $table = 'old_institutes';
    protected $guarded = ['id'];

    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
    
    public function mainCategory()
    {
        return $this->belongsTo(OldCategory::class, 'category_id');
    }
    
    public function categories()
    {
        return $this->belongsToMany(OldCategory::class, 'old_institute_categories', 'institute_id', 'category_id');
    }
    
    public function images()
    {
        return $this->hasMany(OldInstituteImage::class, 'institute_id');
    }
    
    public function rates()
    {
        return $this->hasMany(OldRate::class, 'institute_id');
    }
    
    public function notifications()
    {
        return $this->hasMany(OldNotification::class, 'institute_id');
    }
}
