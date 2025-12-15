<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorDashStep extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'doordash_status',
        'doordash_response',
        'doordash_delivery_id',
    ];
}
