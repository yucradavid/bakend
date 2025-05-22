<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntrepreneurCategory extends Model
{
    // Si tu pivot no usa timestamps:
    public $timestamps = false;

    // Indicamos la tabla explícitamente (opcional si sigue convención)
    protected $table = 'entrepreneur_categories';

    // Como no es una tabla con clave auto‐incremental:
    public $incrementing = false;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'entrepreneur_id',
        'category_id',
    ];
}
