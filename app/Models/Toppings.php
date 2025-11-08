<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toppings extends Model
{
    use HasFactory;
	protected $table = 'toppings';
	protected $fillable = [
        'code',
        'toppingsName',
		'countAs',
		'toppingsImage',
		'price',
		'isPaid',
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
