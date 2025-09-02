<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicHomepage extends Model
{
    protected $fillable = [
        'clinic_id',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'about_text',
        'announcement_title',
        'announcement_body',
        'announcement_image',
    ];

    public function clinic()
    {
        return $this->belongsTo(ClinicInfo::class, 'clinic_id');
    }
}