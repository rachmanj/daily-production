<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPrice extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'fuel_type_id',
        'price_per_liter',
        'effective_date',
    ];

    protected function casts(): array
    {
        return [
            'price_per_liter' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }
}
