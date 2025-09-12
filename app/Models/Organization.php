<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'logo',
        'color_theme',
        'sidebar_bg',
  
    ];

    // If you want the API to include the logo_url attribute
    protected $appends = ['logo_url'];

    // Method to get the logo URL
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function lostItems()
    {
        return $this->hasMany(LostItem::class);
    }

    public function foundItems()
    {
        return $this->hasMany(FoundItem::class);
    }
}