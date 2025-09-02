<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'breed',
        'age',
        'birthday',
        'last_vaccination_date',
        'vaccination_details',
        'image',
        'notes'
    ];

    protected $casts = [
        'birthday' => 'date',
        'last_vaccination_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vaccinations()
    {
        return $this->hasMany(PetVaccination::class);
    }
}
