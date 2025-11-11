<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostItem extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_UNRESOLVED = 'unresolved';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_CLAIMED = 'claimed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'organization_id',
        'title',
        'description',
        'category',
        'location',
        'date_lost',
        'time_lost',
        'image',
        'status',
        'cancellation_reason',
    ];

    protected $casts = [
        'date_lost' => 'date',
    ];

    protected $appends = ['image_url'];

    // 🔗 Image accessor
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // 🔗 Reporter (User who created the lost item report)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔗 Organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // 🔗 Claims
    public function claims()
    {
        return $this->hasMany(Claim::class, 'lost_item_id');
    }

    // 🔗 Photos (for multiple uploads)
    public function photos()
    {
        return $this->hasMany(LostItemPhoto::class);
    }

    // Helper methods for status
    public function isUnresolved()
    {
        return $this->status === self::STATUS_UNRESOLVED;
    }

    public function isUnderReview()
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isClaimed()
    {
        return $this->status === self::STATUS_CLAIMED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_UNRESOLVED, self::STATUS_UNDER_REVIEW]);
    }

    public function cancel($reason)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason
        ]);
    }
}
