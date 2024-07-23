<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'user_role',
        'date_of_birth',
        'login_is_enable',
        'hospital_id',
        'status'
    ];

}
