<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function resturant(){
        return $this->belongsTo(Resturant::class,'resturant_id');
    }
    public function foods(){
        return $this->hasMany(Foods::class,'section_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

}
