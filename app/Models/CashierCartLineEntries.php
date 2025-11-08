<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierCartLineEntries extends Model
{
    use HasFactory;
	protected $table = 'cashiercartlineentries';
	protected $fillable = [
        'code',
        'cartCode',
		'productCode',
		'productType',
		'productName',
		'config',
		'quantity',
		'price',
		'amount',		
		'created_at',
		'updated_at',
    ];
}