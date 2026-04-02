<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchUser extends Model
{
    /** @use HasFactory<\Database\Factories\BranchUserFactory> */
    use HasFactory;


    protected $table = "branch_users";
}
