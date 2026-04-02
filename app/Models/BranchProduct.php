<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchProduct extends Model
{
    /** @use HasFactory<\Database\Factories\BranchProductFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'status',
        'amount'
    ];

    protected $table = 'branch_product';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
