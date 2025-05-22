<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class Entrepreneur extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'association_id',
        'place_id',
        'business_name',
        'ruc',
        'phone',
        'description',
        'profile_image',
        'latitude',
        'longitude',
        'district',
        'status',
    ];

    // Relación con User
    public function user()
{
    return $this->belongsTo(User::class);
}

    // Relación con Association
    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    // Relación con Place
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    // Relación Many-to-Many con Category
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'entrepreneur_categories');
    }

    /**
     * Habilita la inclusión automática de este accesor en el JSON
     * cuando serialices el modelo.
     */
    protected $appends = ['profile_image_url'];

    /**
     * Accessor: devuelve la URL pública absoluta de la imagen de perfil.
     *
     * @return string|null
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (! $this->profile_image) {
            return null;
        }

        // Genera "/storage/entrepreneurs/archivo.jpg"
        $relativePath = Storage::url($this->profile_image);

        // Devuelve "http://tu-dominio.com/storage/entrepreneurs/archivo.jpg"
        return URL::to($relativePath);
    }
    public function reservations()
{
    return $this->hasMany(Reservation::class);
}
public function getLocationAttribute(): string
{
    return "{$this->district}, {$this->place->name}";
}
public function packages()
{
    return $this->hasMany(Package::class);
}

}
