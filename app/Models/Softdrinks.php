<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Softdrinks extends Model
{
    use HasFactory;
	protected $table = 'softdrinks';
	protected $fillable = [
        'code',
        'softdrinks',
		'softDrinkImage',
		'price',
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
        'ratings',
        'reviews',
        'description'
    ];
}
