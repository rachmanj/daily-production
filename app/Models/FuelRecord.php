<?php

namespace App\Models;

use App\Enums\FuelUsageCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRecord extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'daily_entry_id',
        'equipment_id',
        'unit_code',
        'shift_id',
        'fuel_type_id',
        'liters',
        'working_hours',
        'usage_category',
    ];

    protected function casts(): array
    {
        return [
            'liters' => 'decimal:2',
            'working_hours' => 'decimal:2',
            'usage_category' => FuelUsageCategory::class,
        ];
    }

    public function dailyEntry(): BelongsTo
    {
        return $this->belongsTo(DailyEntry::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }
}
