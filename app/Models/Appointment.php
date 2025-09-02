<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['clinic_id', 'owner_name', 'owner_phone', 'status'];

    public function customValues()
    {
        return $this->hasMany(AppointmentFieldValue::class);
    }

    public function clinic()
    {
        return $this->belongsTo(ClinicInfo::class, 'clinic_id');
    }
}
