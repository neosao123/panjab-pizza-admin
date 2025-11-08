<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierCartMaster extends Model
{
    use HasFactory;
	protected $table = 'cashiercartmaster';
	protected $fillable = [
        'code',
        'customerCode',
		'customerName',
		'mobileNumber',
		'address',
		'deliveryType',
		'storeLocation',
		'created_at',
		'updated_at',
    ];
}