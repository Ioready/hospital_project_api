<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permission;

class Role extends Model
{
    
    protected $fillable = ['name'];

    const ROLE_SUPERADMIN = 1;
    const ADMIN = 2;
    const HOSPITAL = 3;
    const DOCTOR = 4;
    const NURSES = 5;
    const ACCOUNTANT = 6;
    const STAFF = 7;
    const EMPLOYEE = 8;
    const PATIENT = 9;


    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
