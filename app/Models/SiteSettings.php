<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model
{
    // Table name 
    protected $table = 'site_settings';

    // Columns that can be mass assigned
    protected $fillable = [
        'key',
        'value',
    ];

    // Timestamps are true since your table has created_at & updated_at
    public $timestamps = true;
}
