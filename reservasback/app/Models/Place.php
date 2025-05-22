<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Place extends Model
{
    protected $fillable = [
        'name',
        'excerpt',
        'activities',
        'stats',
        'image_url',
        'latitude',
        'longitude',
        'category',
    ];


      protected $casts = [
        'activities' => 'array',
        'stats'      => 'array',
      ];

}
