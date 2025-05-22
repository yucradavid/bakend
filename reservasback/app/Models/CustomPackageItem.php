<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPackageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_package_id',
        'product_id',
        'quantity',
    ];

    public function customPackage()
    {
        return $this->belongsTo(CustomPackage::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
