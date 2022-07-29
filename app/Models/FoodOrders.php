<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodOrders extends Model
{
    use HasFactory;
    protected $table = 'food__orders';
    protected $guarded = [];
    protected $with = ['food'];

    public function food(){
        return $this->belongsTo(Food::class,'food_id');
    }

}
