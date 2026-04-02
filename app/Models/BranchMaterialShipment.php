<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchMaterialShipment extends Model
{
    use HasFactory;

    protected $table = 'branch_material_shipments';

    protected $fillable = [
        'branch_id',
        'branch_material_id',
        'material_id',
        'order_id',
        'quantity',
        'unit',
        'status',
        'transaction_date',
        'shipment_date',
        'sent_date',
        'consumer_type',
        'consumer_name',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'transaction_date' => 'date',
        'shipment_date' => 'date',
        'sent_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchMaterial()
    {
        return $this->belongsTo(BranchMaterial::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope for sent (shipment) records
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for consumed records
     */
    public function scopeConsumed($query)
    {
        return $query->where('status', 'consumed');
    }
}
