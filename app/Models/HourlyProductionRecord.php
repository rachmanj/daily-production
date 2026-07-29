<?php

namespace App\Models;

use App\Enums\MaterialType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourlyProductionRecord extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'daily_entry_id',
        'equipment_id',
        'unit_code',
        'shift_id',
        'pit_id',
        'material_type',
        'hour_slot',
        'tonnage',
        'location',
        'loader_info',
    ];

    protected function casts(): array
    {
        return [
            'material_type' => MaterialType::class,
            'hour_slot' => 'integer',
            'tonnage' => 'decimal:2',
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

    public function pit(): BelongsTo
    {
        return $this->belongsTo(Pit::class);
    }
}
