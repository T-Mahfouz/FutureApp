<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function news()
    {
        return $this->belongsTo(News::class);
    }


    /**
     * Many-to-many relationship with cities
     */
    public function cities()
    {
        return $this->belongsToMany(City::class, 'notification_cities');
    }

    /**
     * Get the related content (service or news)
     */
    public function getRelatedContentAttribute()
    {
        if ($this->service_id && $this->service) {
            return [
                'type' => 'service',
                'item' => $this->service
            ];
        } elseif ($this->news_id && $this->news) {
            return [
                'type' => 'news',
                'item' => $this->news
            ];
        }
        
        return null;
    }

    /**
     * Check if notification has both service and news (should not happen)
     */
    public function hasBothServiceAndNews()
    {
        return $this->service_id && $this->news_id;
    }
}
