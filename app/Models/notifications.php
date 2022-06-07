<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notifications extends Model
{
    use HasFactory;
    protected $guarded = [];

     public function to_user()
    {
        return $this->belongsTo(User::class, 'to_user');
    }
    public function from_user()
    {
        return $this->belongsTo(User::class, 'from_user');
    }
}
