<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialbases extends Model
{
    use HasFactory;
	protected $table = 'specialbases';
	protected $fillable = [
        'code',
        'specialbase',
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
    ];
}
