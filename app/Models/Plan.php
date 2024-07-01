<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Plan extends Model
{
   
    protected $fillable = ['plan_name','patient_limit','doctor_limit','monthly_price','yearly_price','permission_module','description','avatar','status'];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
