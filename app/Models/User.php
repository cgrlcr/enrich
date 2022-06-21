<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'token',
    ];

    protected $hidden = [
        'password',
        'token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function userToken()
    {
        return $this->morphOne(Token::class, 'authable');
    }
}
