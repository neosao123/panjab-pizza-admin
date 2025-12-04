<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSLog extends Model
{
    use HasFactory;

    protected $table = "sms_logs";

    protected $fillable = [
        'template_id',
        'template_message',
        'mobile_number',
        'customer_id',
        'status',
        'message_response',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get pending SMS logs
     */
    public static function getPending()
    {
        return self::where('status', 'pending')->get();
    }

    /**
     * Get failed SMS logs
     */
    public static function getFailed()
    {
        return self::where('status', 'failed')->get();
    }

    /**
     * Get sent SMS logs
     */
    public static function getSent()
    {
        return self::where('status', 'sent')->get();
    }
}
