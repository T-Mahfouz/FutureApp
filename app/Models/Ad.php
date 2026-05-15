<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'city_id',
        'category_id',
        'service_id',
        'link',
        'image_id',
        'expiration_date',
    ];

    protected $casts = [
        'expiration_date' => 'datetime'
    ];

    public function city()
    {
        return $this->belongsTo(City::class)->withDefault();
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withDefault();
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /**
     * Check if ad is currently active (not expired)
     */
    public function isActive()
    {
        return !$this->expiration_date || $this->expiration_date->isFuture();
    }

    /**
     * Check if ad is expired
     */
    public function isExpired()
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    /**
     * Get the display status of the ad
     */
    public function getStatusAttribute()
    {
        return $this->isActive() ? 'active' : 'expired';
    }

    /**
     * Scope for active ads
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expiration_date')
              ->orWhere('expiration_date', '>', now());
        });
    }

    /**
     * Scope for expired ads
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiration_date')
                    ->where('expiration_date', '<=', now());
    }
}