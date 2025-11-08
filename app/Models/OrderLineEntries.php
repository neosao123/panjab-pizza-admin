<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLineEntries extends Model
{
  use HasFactory;

  protected $table = 'orderlineentries';

  protected $fillable = [
    'code',
    'pid',
    'orderCode',
    'productCode',
    'productName',
    'productType',
    'config',
    'quantity',
    'price',
    'amount',
    'pizzaSize',
    'comments',
    'created_at',
    'updated_at',
    'pizzaPrice',
  ];

  public $incrementing = true;

  // The data type of the primary key.
  protected $keyType = 'int';

  // The primary key column name.
  protected $primaryKey = 'id';
  
}
