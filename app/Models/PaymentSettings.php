<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSettings extends Model
{
    use HasFactory;


    protected $table = 'payment_settings';

    protected $fillable =[
    'payment_mode',
    'test_secret_key',
    'live_secret_key',
    'test_client_id',
    'live_client_id',
    'webhook_secret_key',
    'webhook_secret_live_key',
    'payment_gateway',
    'isActive',
    'isDelete',
    ];
}
