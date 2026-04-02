<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence',
        'invoice_number',
        'order_number',
        'date',
        'customer_phone',
        'description',
        'rental_fees',
        'service_fees',
        'total_amount',
        'contract_id',
        'created_by_employee_id',
    ];

    protected $casts = [
        'date' => 'date',
        'rental_fees' => 'decimal:2',
        'service_fees' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Invoice $invoice) {
            if (empty($invoice->sequence)) {
                $invoice->sequence = (int) (static::max('sequence') ?? 0) + 1;
            }
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . str_pad((string)$invoice->sequence, 8, '0', STR_PAD_LEFT);
            }

            if (empty($invoice->total_amount)) {
                $invoice->total_amount = (float)($invoice->rental_fees ?? 0) + (float)($invoice->service_fees ?? 0);
            }
        });
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'created_by_employee_id');
    }
}





