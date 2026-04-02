<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    /** @use HasFactory<\Database\Factories\RanksFactory> */
    use HasFactory;

     protected $fillable = [
        'name',
        'title',
        'image',
        'min_stars',
        'max_stars',
        'points_increment',
        'stars_increment',
        'description',
        'badge_color',
    ];


}
