<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SidesMaster extends Model
{
    use HasFactory;
    protected $table = 'sidemaster';
    protected $fillable = [
        'code',
        'sidename',
        'image',
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
