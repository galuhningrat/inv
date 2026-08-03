<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'level',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class, 'requester_id');
    }

    public function approvedRequests()
    {
        return $this->hasMany(AssetRequest::class, 'approved_by');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'recorded_by');
    }
}