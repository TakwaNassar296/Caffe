<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequestApproval extends Model
{
    protected $table = 'material_request_approvals';

    protected $fillable = [
        'request_material_id',
        'admin_id',
        'action',
        'quantity',
        'comment',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function requestMaterial()
    {
        return $this->belongsTo(RequestMaterial::class, 'request_material_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
