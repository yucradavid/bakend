<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningHour extends Model {
    protected $fillable = [
        'entrepreneur_id', 'day', 'opening_time', 'closing_time', 'is_closed'
    ];

    public function entrepreneur(): BelongsTo {
        return $this->belongsTo(Entrepreneur::class);
    }
}
