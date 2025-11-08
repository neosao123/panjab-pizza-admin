<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class CrustType extends Model
{
    use HasFactory;
    protected $table = 'crust_type';
    protected $fillable = [
        'code',
        'crustType',
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
