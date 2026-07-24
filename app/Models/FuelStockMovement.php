<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelStockMovement extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'fuel_receipt_id',
        'site_id',
        'type',
        'liters',
        'movement_date',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'liters' => 'decimal:2',
            'movement_date' => 'date',
        ];
    }

    public function fuelReceipt(): BelongsTo
    {
        return $this->belongsTo(FuelReceipt::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
