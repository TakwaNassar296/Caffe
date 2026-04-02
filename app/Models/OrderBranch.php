<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderBranch extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'status','branch_material_id','reason_of_cancel','branch_id'
    ];

    public function BranchMaterial()
    {
      return $this->belongsTo(BranchMaterial::class);
    }

     public function branch()
    {
      return $this->belongsTo(Branch::class);
    }

}
