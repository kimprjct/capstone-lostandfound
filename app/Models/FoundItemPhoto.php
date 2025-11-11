<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundItemPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'found_item_id',
        'path',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->path ? asset('storage/' . $this->path) : null;
    }

    public function foundItem()
    {
        return $this->belongsTo(FoundItem::class);
    }
}
