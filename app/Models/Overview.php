<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overview extends Model
{
    protected $table='overviews';
    use HasFactory;
    protected $fillable=['name_overview','value','image'];
}
