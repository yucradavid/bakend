<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'entrepreneur_id',
        'name',
        'description',
        'price',
        'image_url',
        'is_active',
    ];

    // Relaciones
    public function entrepreneur()
    {
        return $this->belongsTo(Entrepreneur::class);
    }

public function products()
{
    return $this->belongsToMany(Product::class, 'package_product')
                ->withPivot('id')
                ->withTimestamps(); // ✅ Ya es válido
}
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
public function images()
{
    return $this->hasMany(PackageImage::class);
}

}
