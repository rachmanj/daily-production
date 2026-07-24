<?php

namespace App\Models;

use App\Enums\PitOwner;
use App\Enums\PlanMetric;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanTarget extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'monthly_plan_id',
        'pit_id',
        'metric',
        'owner',
        'target_value',
    ];

    protected function casts(): array
    {
        return [
            'metric' => PlanMetric::class,
            'owner' => PitOwner::class,
            'target_value' => 'decimal:2',
        ];
    }

    public function monthlyPlan(): BelongsTo
    {
        return $this->belongsTo(MonthlyPlan::class);
    }

    public function pit(): BelongsTo
    {
        return $this->belongsTo(Pit::class);
    }
}
