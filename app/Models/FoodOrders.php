<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodOrders extends Model
{
    use HasFactory;
    protected $table = 'food__orders';
    protected $guarded = [];
    protected $with = ['food','orders'];

    public function food(){
        return $this->belongsTo(Food::class,'food_id');
    }
    public function orders(){
        return $this->belongsTo(Orders::class,'order_id');
    }

}
