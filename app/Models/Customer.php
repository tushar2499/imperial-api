<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'mobile',
        'name',
        'gender',
        'age',
        'address',
        'passport_no',
        'nid',
        'nationality',
        'email',
        'password',
        'photo',
        'total_trips',
        'total_tickets',
        'total_cancelled_tickets',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'age' => 'integer',
        'total_trips' => 'integer',
        'total_tickets' => 'integer',
        'total_cancelled_tickets' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
