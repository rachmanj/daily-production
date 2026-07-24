<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelReceipt extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'receipt_date',
        'liters',
        'gi_number',
        'supplier',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'liters' => 'decimal:2',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function fuelStockMovements(): HasMany
    {
        return $this->hasMany(FuelStockMovement::class);
    }
}
