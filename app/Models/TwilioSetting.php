<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwilioSetting extends Model
{
    use HasFactory;


    protected $table = 'twilio_settings';

    protected $fillable = [
        'twilio_session_id',
        'twilio_auth_id',
        'twilio_number',
        'twilio_mode',
        'isActive',
        'isDelete'
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'isDelete' => 'boolean',
    ];

     public static function getActiveSettings()
    {
        return self::where('isActive', 1)
                   ->where('isDelete', 0)
                   ->first();
    }
}
