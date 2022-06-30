<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'favorites';
    // protected $with = ['user','resturant'];

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }
    // public function resturant()
    // {
    //     return $this->belongsTo(Resturant::class, 'resturant_id');
    // }
    public function favouriteable(){
        return $this->morphTo();
    }
}
