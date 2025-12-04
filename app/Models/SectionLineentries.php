<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionLineentries extends Model
{
    use HasFactory;

    protected $table = 'section_lineentries';
    
    protected $fillable = [
        'id',
        'section_id',
        'image',
        'title',
        'counter',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    // Relationship with section
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}