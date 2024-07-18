<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'discount',
        'expiry_date',
        'limit'
    ];

    public function used_coupon()
    {
        return $this->hasMany('App\Models\UserCoupon', 'coupon', 'id')->count();
    }
}
