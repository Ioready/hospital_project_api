<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

   
    protected $fillable = [
        'title','email','password','frontend_website_link','address','phone','language','package_duration','Country','package','patient_limit','doctor_limit','permitted_modules','price','deposit_type','do_you_want_trial_version','status','logo'
    ];


}
