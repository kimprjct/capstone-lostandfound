<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'found_item_id',
        'lost_item_id',
        'organization_id',
        'claim_reason',
        'photo',           // ✅ keep, after migration
        'location',
        'claim_datetime',  // ✅ corrected
        'time_lost',
        'time_found',
        'status',
        'claim_code',
        'resolved_at',
        'resolved_by',
        'rejection_reason'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'claim_datetime' => 'datetime', // ✅ corrected
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function foundItem()
    {
        return $this->belongsTo(FoundItem::class);
    }

    public function lostItem()
    {
        return $this->belongsTo(LostItem::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Generate a unique claim code
     */
    public static function generateClaimCode()
    {
        do {
            $code = 'CLAIM-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('claim_code', $code)->exists());
        
        return $code;
    }
}
