<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Sections extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];
    protected $with=["resturant"];

    public function resturant(){
        return $this->belongsTo(Resturant::class,'resturant_id');
    }
    public function foods(){
        return $this->hasMany(Food::class,'section_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

}
