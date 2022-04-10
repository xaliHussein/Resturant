<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    // public function foods(){
    //     return $this->belongsToMany(Food::class,'order_foods','order_id','food_id');
    // }
    public function resturant(){
        return $this->belongsTo(Resturant::class,'resturant_id');
    }
}
