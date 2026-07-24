<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteInfo extends Model
{
    protected $table = 'site_info';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'daily_entry_id',
        'weather',
        'rain_hours',
        'slippery_hours',
        'manpower_plan',
        'manpower_actual',
        'safety_notes',
        'fuel_stock_liters',
    ];

    protected function casts(): array
    {
        return [
            'rain_hours' => 'decimal:2',
            'slippery_hours' => 'decimal:2',
            'fuel_stock_liters' => 'decimal:2',
        ];
    }

    public function dailyEntry(): BelongsTo
    {
        return $this->belongsTo(DailyEntry::class);
    }
}
