<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storelocation extends Model
{
    use HasFactory;

    protected $table = 'storelocation';
 
    public function province()
    {
        return $this->belongsTo(Province::class, 'tax_province_id', 'id');
    }
}
