<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteMaterial extends Model
{
    /** @use HasFactory<\Database\Factories\WasteMaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'material_id',
        'unit',
        'quantity',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
