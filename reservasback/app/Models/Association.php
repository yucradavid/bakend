<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Association extends Model {
    protected $fillable = ['name', 'description', 'region'];

    public function entrepreneurs(): HasMany {
        return $this->hasMany(Entrepreneur::class);
    }
}
