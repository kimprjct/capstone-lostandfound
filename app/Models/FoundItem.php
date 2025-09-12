<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'title',
        'description',
        'category',
        'location',
        'date_found',
        'image',
        'status'
    ];

    protected $casts = [
        'date_found' => 'date',
    ];
    
    protected $appends = ['image_url'];
    
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }
}
