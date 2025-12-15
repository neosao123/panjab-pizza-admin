<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

      protected $fillable = [
        'external_business_id',
        'name',
        'phone_number',
        'description',
        'activation_status'
    ];
}
