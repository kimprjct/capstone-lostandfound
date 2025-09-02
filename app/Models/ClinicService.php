<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicService extends Model
{
    protected $fillable = [
        'clinic_id',
        'name',
        'description',
        'price',
        'icon',
        'image_path',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function clinic()
    {
        return $this->belongsTo(ClinicInfo::class, 'clinic_id');
    }
}