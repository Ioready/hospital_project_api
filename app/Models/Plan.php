<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order;

class Plan extends Model
{
   
    protected $fillable = ['card_label','card_title','title_description','price','type','exclusive_and_including_tax','tax_name','tax_percentage','text_area','button_name','button_link','feature_title','feature_list','user_permission','permission_by_module','images','status'];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
