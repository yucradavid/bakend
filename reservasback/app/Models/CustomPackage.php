<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'total_amount',
        'status',
    ];

    /**
     * Relación: un paquete personalizado pertenece a un usuario (cliente)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: productos que forman parte del paquete personalizado
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'custom_package_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Relación: tabla intermedia para poder acceder a cada ítem con cantidad
     */
    public function items()
    {
        return $this->hasMany(CustomPackageItem::class);
    }

    /**
     * Scope para paquetes confirmados
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmado');
    }

    /**
     * Calcular el monto total basado en productos
     */
    public function calculateTotal()
    {
        return $this->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });
    }

public function latestReservation()
{
    return $this->hasOne(Reservation::class)
                ->whereNotNull('custom_package_id')
                ->latestOfMany(); // Esto obtiene la reserva más reciente
}
}
