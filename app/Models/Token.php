<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $fillable = [
        'authable_type',
        'authable_id',
        'key',
        'status',
    ];

    public function authable()
    {
        return $this->morphTo();
    }
}
