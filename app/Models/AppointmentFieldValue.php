<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentFieldValue extends Model
{
    protected $fillable = ['appointment_id', 'clinic_field_id', 'value'];
    
    protected $casts = [
        'value' => 'json',
    ];

    public function field()
    {
        return $this->belongsTo(ClinicField::class, 'clinic_field_id');
    }
    
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
