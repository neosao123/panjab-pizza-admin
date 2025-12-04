<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';
    
    protected $fillable = [
        'id',
        'title',
        'subTitle',
        'isActive',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    // Relationship with section line entries
    public function lineentries()
    {
        return $this->hasMany(SectionLineentries::class, 'section_id', 'id');
    }
}