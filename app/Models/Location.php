<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use HasFactory;
    protected $fillable = [
        'city_id',
        'area_id',
        'street'
    ];

    public function city(): BelongsTo {
        return $this->belongsTo(City::class);
    }

    public function area(): BelongsTo {
        return $this->belongsTo(Area::class);
    }
}
