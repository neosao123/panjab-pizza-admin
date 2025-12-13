<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorDash extends Model
{
    use HasFactory;

    protected $table = 'door_dashes';

    protected $fillable = [
        'mode',
        'test_developer_id',
        'live_developer_id',
        'test_key_id',
        'live_key_id',
        'test_signing_secret',
        'live_signing_secret',
    ];
}
