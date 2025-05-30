<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsImage extends Model
{
    protected $guarded = ['id'];

    public function news()
    {
        return $this->belongsTo(News::class);
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
