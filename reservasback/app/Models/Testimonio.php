<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Testimonio extends Model
{
    protected $fillable = [
        'user_id', 'nombre', 'estrellas', 'comentario',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }


}
