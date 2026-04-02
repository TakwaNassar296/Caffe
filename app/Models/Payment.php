<?php

namespace App\Models;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_date','contract_uuid','payment_method','tran_currency' ,'name', 'amount', 'payment_date', 'status'
    ];

    // Define relationships
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_uuid', 'uuid');
    }
   

}
 