<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    // The table name (optional if follows Laravel convention 'sms_logs')
    protected $table = 'sms_logs';

    // Fields that can be mass assigned
    protected $fillable = [
        'user_id',
        'phone_number',
        'message',
        'type',
        'sms_id',
        'sent_at',
    ];

    // If you want to disable timestamps (created_at, updated_at) use this:
    // public $timestamps = false;

    // Define relationship to User (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Cast sent_at to a datetime object automatically
    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
