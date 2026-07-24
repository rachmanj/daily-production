<?php

namespace App\Models;

use App\Enums\ProductionActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRecord extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'daily_entry_id',
        'pit_id',
        'shift_id',
        'ob_removal_bcm',
        'coal_getting_ton',
        'coal_hauling_ton',
        'activity',
        'truck_count',
    ];

    protected function casts(): array
    {
        return [
            'ob_removal_bcm' => 'decimal:2',
            'coal_getting_ton' => 'decimal:2',
            'coal_hauling_ton' => 'decimal:2',
            'activity' => ProductionActivity::class,
            'truck_count' => 'integer',
        ];
    }

    public function dailyEntry(): BelongsTo
    {
        return $this->belongsTo(DailyEntry::class);
    }

    public function pit(): BelongsTo
    {
        return $this->belongsTo(Pit::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
