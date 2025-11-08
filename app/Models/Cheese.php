<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cheese extends Model
{
    use HasFactory;
	protected $table = 'cheese';
	protected $fillable = [
        'code',
        'cheese',
        'isActive',
        'isDelete'
    ];
}
