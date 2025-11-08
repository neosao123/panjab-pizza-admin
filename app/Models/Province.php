<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'province_tax_rates';
    
    public function storeLocations()
    {
        return $this->hasMany(StoreLocation::class, 'tax_province_id', 'id');
    }
}
