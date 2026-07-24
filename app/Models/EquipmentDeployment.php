<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDeployment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'daily_entry_id',
        'equipment_id',
        'unit_code',
        'pit_id',
        'shift_id',
        'prod_ob_bcm',
        'prod_coal_ton',
        'operator_name',
    ];

    protected function casts(): array
    {
        return [
            'prod_ob_bcm' => 'decimal:2',
            'prod_coal_ton' => 'decimal:2',
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
