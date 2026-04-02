<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestMaterial extends Model
{
    protected $table = 'request_materials';

    protected $fillable = [
        'branch_id',
        'material_id',
        'quantity',
        'status',
        'approved_quantity',
        'comment',
        'delivery_status',
        'delivery_feedback',
        'delivery_confirmed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'approved_quantity' => 'decimal:2',
        'delivery_confirmed_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function approvals()
    {
        return $this->hasMany(MaterialRequestApproval::class, 'request_material_id');
    }

    public function latestApproval()
    {
        return $this->hasOne(MaterialRequestApproval::class, 'request_material_id')->latestOfMany();
    }
}
