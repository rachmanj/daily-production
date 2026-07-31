<?php

namespace App\Models;

use App\Enums\MaterialType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripProductionRecord extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'daily_entry_id',
        'excavator_id',
        'excavator_code',
        'hauler_id',
        'hauler_code',
        'shift_id',
        'material_type',
        'hour_slot',
        'truck_capacity_bcm',
        'volume_bcm',
        'load_percent',
        'trip_count',
        'excavator_type',
        'hauler_type',
    ];

    protected function casts(): array
    {
        return [
            'material_type' => MaterialType::class,
            'hour_slot' => 'integer',
            'truck_capacity_bcm' => 'decimal:2',
            'volume_bcm' => 'decimal:2',
            'load_percent' => 'decimal:2',
            'trip_count' => 'decimal:2',
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
}
