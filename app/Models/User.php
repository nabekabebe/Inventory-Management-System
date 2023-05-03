<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    use HasUuids;
    public $incrementing = false;
    protected $fillable = [
        'is_manager',
        'managing_token',
        'password',
        'full_name',
        'email',
        'phone_number'
    ];
    protected $casts = [
        'is_manager' => 'boolean'
    ];
    protected $hidden = ['password'];
}
