<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device_token extends Model
{
    use HasFactory;

    protected $fillable=['user_id','token'];

    public function users(){
        return $this->belongsTo(User::class);
    }
}
