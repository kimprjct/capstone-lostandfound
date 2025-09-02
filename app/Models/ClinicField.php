<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicField extends Model
{
    protected $table = 'clinic_fields';

    protected $fillable = [
        'clinic_id',
        'label',
        'type',
        'options',
        'required',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
    ];

    public function clinicInfo()
    {
        return $this->belongsTo(\App\Models\ClinicInfo::class, 'clinic_id');
    }
    
}
