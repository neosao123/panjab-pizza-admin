<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory;

    protected $table = 'picture'; // your table name

    protected $fillable = [
        'title',
        'product_url',
        'image',
        'isActive',
        'isDelete',
        'deleteDate',
    ];
        public $timestamps = false; 

}
