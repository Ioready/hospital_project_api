<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'mobile_number',
        'country_id',
        'state',
        'city',
        'postal_code',
        'avatar',
        'status'
        // Add other profile fields here
    ];

    // Define the relationship to the User model if needed
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
