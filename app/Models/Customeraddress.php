<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customeraddress extends Model
{
    use HasFactory;
	protected $table = 'customeraddress';
	protected $fillable = [
        'code',
		'customerCode',
        'street',
		'city',
		'landmark',
		'zipcode',
        'isActive',
        'isDelete',
		'addID',
		'addIP',
		'addDate',
		'editID',
		'editIP',
		'editDate',
		'deleteID',
		'deleteIP',
		'deleteDate',
		
    ];
}
