<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralTransaction extends Model
{
    use HasFactory;


    protected $fillable = [
        'hospital_id',
        'plan_id',
        'plan_price',
        'commission',
        'minimum_threshold_amount',
        'referral_code',
        'status',
        'req_amount',
    ];

    public function getPlan()
    {
        return $this->hasOne('App\Models\Plan', 'id', 'plan_id');
    }

    public function getUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'hospital_id');
    }

    public function getHospital()
    {
        return $this->hasOne('App\Models\User', 'referral_code', 'referral_code');
    }
}
