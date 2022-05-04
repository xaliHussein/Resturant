<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resturant extends Model
{
    use HasFactory;
    protected $guarded = [];
     protected $with = ['user'];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function sections(){
        return $this->hasMany(Sections::class,'resturant_id');
    }
}
