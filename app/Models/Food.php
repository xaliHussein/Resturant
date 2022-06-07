<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends Model
{
    use HasFactory ,SoftDeletes;
    protected $guarded = [];
    protected $with = ["section"];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function section(){
        return $this->belongsTo(Sections::class,'section_id');
    }
}
