<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'address',
        'phone_number',
        'email',
        'password',
        'role',
        'UserTypeID',
        'organization_id',
        'verification_code',
        'verification_code_expires_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function lostItems()
    {
        return $this->hasMany(LostItem::class);
    }

    public function foundItems()
    {
        return $this->hasMany(FoundItem::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }
    
    public function resolvedClaims()
    {
        return $this->hasMany(Claim::class, 'resolved_by');
    }

    public function notifications()
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable');
    }

    /**
     * Accessor to provide a legacy role value derived from UserTypeID when role is missing.
     */
    public function getRoleAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        $typeId = $this->attributes['UserTypeID'] ?? null;
        if ($typeId === 1) {
            return 'admin';
        }
        if ($typeId === 2) {
            return 'tenant';
        }
        if ($typeId === 3) {
            return 'user';
        }

        return null;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }

    /**
     * Generate a 6-digit verification code and set expiration time.
     *
     * @return string
     */
    public function generateVerificationCode()
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10), // Code expires in 10 minutes
        ]);
        return $code;
    }

    /**
     * Verify the provided code.
     *
     * @param string $code
     * @return bool
     */
    public function verifyCode($code)
    {
        if (!$this->verification_code || !$this->verification_code_expires_at) {
            return false;
        }

        if (now()->isAfter($this->verification_code_expires_at)) {
            return false; // Code has expired
        }

        if ($this->verification_code !== $code) {
            return false; // Code doesn't match
        }

        // Code is valid, mark email as verified and clear the code
        $this->update([
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        return true;
    }
}
