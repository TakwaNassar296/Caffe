<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPayment extends Model
{
    /** @use HasFactory<\Database\Factories\UserPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        "user_id",
        'name',
        'card_number',
        'cvv',
        'expire_date',
    ];
}
