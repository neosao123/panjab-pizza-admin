<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCartLineEntries extends Model
{
    use HasFactory;
	protected $table = 'customercartlineentries';
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