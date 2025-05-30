<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldCategory extends Model
{
    protected $table = 'old_categories';
    protected $guarded = ['id'];

    public function city()
    {
        return $this->belongsTo(OldCity::class, 'city_id');
    }
    
    public function parent()
    {
        return $this->belongsTo(OldCategory::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(OldCategory::class, 'parent_id');
    }
    
    public function institutes()
    {
        return $this->belongsToMany(OldInstitute::class, 'old_institute_categories', 'category_id', 'institute_id');
    }
}
