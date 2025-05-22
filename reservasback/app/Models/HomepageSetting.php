<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_text',
        'title_color',
        'title_size',
        'description',
        'background_color',
        'image_path',
    ];
    protected $casts = [
        'image_path' => 'array',
        'active'     => 'boolean',
    ];
}
