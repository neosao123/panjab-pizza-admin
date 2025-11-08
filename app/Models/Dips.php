<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dips extends Model
{
    use HasFactory;
    protected $table = 'dips';
    protected $fillable = [
        'code',
        'dips',
        'dipsImage',
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
        'reviews'
    ];
}
