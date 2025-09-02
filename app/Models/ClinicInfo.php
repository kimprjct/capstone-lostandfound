<?php

// app/Models/ClinicInfo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'clinic_name',
        'address',
        'contact_number',
        'is_open',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clinicFields()
{
    return $this->hasMany(\App\Models\ClinicField::class, 'clinic_id');
}
public function homepage()
{
    return $this->hasOne(\App\Models\ClinicHomepage::class, 'clinic_id');
}

public function services()
{
    return $this->hasMany(\App\Models\ClinicService::class, 'clinic_id')->orderBy('order');
}
    
}
