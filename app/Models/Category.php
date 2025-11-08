<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Category extends Model
{
  use HasFactory;

  protected $table = 'signaturepizzacategory';

  public function signaturePizzas()
  {
    return $this->hasMany(SignaturePizza::class, 'category_code', 'code');
  }


}
