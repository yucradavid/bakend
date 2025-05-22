<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'image_url',
        'note',
        'status',
        'operation_code',
        'receipt_url',
        'is_confirmed',
        'confirmation_time',
        'confirmation_by',
        'confirmed_at',
        'rejected_at',
        'payment_method',
        'payment_type',       // ✅ nuevo campo
        'payment_location',   // ✅ nuevo campo
    ];

    protected $casts = [
        'is_confirmed'      => 'boolean',
        'confirmation_time' => 'datetime',
        'confirmed_at'      => 'datetime',
        'rejected_at'       => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // Relaciones
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Usuario que confirmó el pago.
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmation_by');
    }

    // Accesores útiles

    /**
     * Diferencia en horas entre pago reportado y confirmado.
     */
    public function getConfirmationDelayHoursAttribute(): ?float
    {
        if ($this->confirmed_at && $this->confirmation_time) {
            return $this->confirmed_at->diffInHours($this->confirmation_time, false);
        }
        return null;
    }

    /**
     * Fecha legible para mostrar en el panel admin (ej. "10 mayo 2025, 11:00 AM").
     */
    public function getConfirmedAtFormattedAttribute(): ?string
    {
        return $this->confirmed_at ? $this->confirmed_at->translatedFormat('d F Y, h:i A') : null;
    }

    public function getConfirmationTimeFormattedAttribute(): ?string
    {
        return $this->confirmation_time ? $this->confirmation_time->translatedFormat('d F Y, h:i A') : null;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'efectivo'       => 'Efectivo en oficina',
            'yape'           => 'Yape físico',
            'plin'           => 'Plin',
            'transferencia'  => 'Transferencia bancaria',
            default          => ucfirst($this->payment_method ?? 'No especificado'),
        };
    }

    /**
     * Etiqueta para el tipo de pago.
     */
    public function getPaymentTypeLabelAttribute(): string
    {
        return match($this->payment_type) {
            'virtual'    => 'Pago virtual',
            'presencial' => 'Pago presencial en oficina',
            default      => 'No especificado',
        };
    }
}
