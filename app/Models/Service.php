<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id', 
        'image_id', 
        'user_id',
        'name',
        'parent_id',
        'address',
        'brief_description',
        'description',
        'lat',
        'lon',
        'website',
        'youtube',
        'facebook',
        'whatsapp',
        'instagram',
        'telegram',
        'video_link',
        'valid',
        'is_request',
        'is_add',
        'arrangement_order',
        'requested_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'rejected_at',
        'rejected_by'
    ];

    protected $casts = [
        'valid' => 'boolean',
        'is_request' => 'boolean',
        'is_add' => 'boolean',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        /* 'lat' => 'decimal:8',
        'lon' => 'decimal:8', */
    ];

    protected $attributes = [
        'valid' => false,
        'is_request' => false,
        'is_add' => false,
        'arrangement_order' => 1,
    ];

    // Relationships
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(Admin::class, 'rejected_by');
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'service_categories');
    }

    public function images()
    {
        return $this->belongsToMany(Media::class, 'service_images', 'service_id', 'image_id')
            ->withPivot('id');
    }

    public function phones()
    {
        return $this->hasMany(ServicePhone::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }
    
    public function subServices()
    {
        return $this->hasMany(Service::class, 'parent_id');
    }

    public function parentService()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Helper methods
    public function averageRating()
    {
        return $this->rates()->avg('rate') ?? 0;
    }

    public function isRequest()
    {
        return $this->is_request;
    }

    public function isPending()
    {
        return $this->is_request && !$this->approved_at && !$this->rejected_at;
    }

    public function isApproved()
    {
        return $this->approved_at !== null;
    }

    public function isRejected()
    {
        return $this->rejected_at !== null;
    }

    public function getStatusAttribute()
    {
        if ($this->isRejected()) {
            return 'rejected';
        }
        if ($this->isApproved()) {
            return $this->valid ? 'active' : 'inactive';
        }
        if ($this->isPending()) {
            return 'pending';
        }
        return $this->valid ? 'active' : 'inactive';
    }

    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return '<span class="badge badge-warning">Pending Review</span>';
            case 'rejected':
                return '<span class="badge badge-danger">Rejected</span>';
            case 'active':
                return '<span class="badge badge-success">Active</span>';
            case 'inactive':
                return '<span class="badge badge-secondary">Inactive</span>';
            default:
                return '<span class="badge badge-light">Unknown</span>';
        }
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('is_request', true)
                    ->whereNull('approved_at')
                    ->whereNull('rejected_at');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeRejected($query)
    {
        return $query->whereNotNull('rejected_at');
    }

    public function scopeUserRequests($query)
    {
        return $query->where('is_request', true);
    }

    public function scopeAdminCreated($query)
    {
        return $query->where('is_request', false);
    }

    // Actions
    public function approve($adminId = null)
    {
        $this->update([
            'valid' => true,
            'approved_at' => now(),
            'approved_by' => $adminId,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ]);
    }

    public function reject($reason = null, $adminId = null)
    {
        $this->update([
            'valid' => false,
            'rejected_at' => now(),
            'rejected_by' => $adminId,
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }


    private function requestStatus()
    {
        if (!$this->is_request) 
            return null;
        
        if (!$this->approved_at & !$this->rejected_at) {
            return 'Pending';
        }
        else if ($this->approved_at != null) {
            return 'Approved';
        } else {
            return 'Rejected';
        }
    }
}