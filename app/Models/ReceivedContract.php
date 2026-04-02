<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivedContract extends Model
{
    use HasFactory;

    protected $fillable=[
        
        'contract_id',
        'employee_id',
        'notes',
        'date_of_received'
    ];

 
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
    
}
