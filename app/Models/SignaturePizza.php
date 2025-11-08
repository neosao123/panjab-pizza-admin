<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SignaturePizza extends Model
{
  use HasFactory;

  protected $table = 'signaturepizza';

  protected $fillable = [
    'code',
    'category_code',
    'pizza_name',
    'pizza_subtitle',
    'pizza_image',
    'pizza_prices',
    'cheese',
    'curst',
    'curst_type',
    'special_base',
    'spices',
    'sauce',
    'cook',
    'topping_as_1',
    'topping_as_2',
    'topping_as_free',
    'isActive',
    'isDelete',
    'addID',
    'addIP',
    'addDate',
    'editIP',
    'editID',
    'editDate',
    'deleteID',
    'deleteIP',
    'deleteDate',
	'description'
  ];

  public $timestamps = false;

  protected $casts = [
    'pizza_prices'=>'array',
    'cheese'=>'array',
    'crust'=>'array',
    'crust_type'=>'array',
    'special_base'=>'array',
    'spices'=>'array',
    'sauce'=>'array',
    'cook'=>'array',
    'topping_as_1' => 'array',
    'topping_as_2' => 'array',
    'topping_as_free' => 'array'
  ];

  public function category()
  {
    return $this->belongsTo(Category::class, 'category_code', 'code');
  }
}
