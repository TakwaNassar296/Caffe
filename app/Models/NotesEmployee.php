<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotesEmployee extends Model
{
    use HasFactory;
    protected $fillable=[
        'employee_id','notes_by_manger'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
}
