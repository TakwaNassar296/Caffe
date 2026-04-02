<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundableContract extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'contract_id',
        'employee_id',
        'has_draft_contract',
        'refund_amount',
        'notes',
        'admin_confirmed',
    ];

     
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
