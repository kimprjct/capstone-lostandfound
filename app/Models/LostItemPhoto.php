<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostItemPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'lost_item_id',
        'path',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->path ? asset('storage/' . $this->path) : null;
    }

    public function lostItem()
    {
        return $this->belongsTo(LostItem::class);
    }
}
