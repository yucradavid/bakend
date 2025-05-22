<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model {
    protected $fillable = ['name', 'icon'];

    public function entrepreneurs(): BelongsToMany {
        return $this->belongsToMany(Entrepreneur::class, 'entrepreneur_categories');
    }

    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, 'product_category');
    }

}
