<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldNewsImage extends Model
{
    protected $table = 'old_news_images';
    protected $guarded = ['id'];
    public function news()
    {
        return $this->belongsTo(OldNews::class, 'news_id');
    }
}
