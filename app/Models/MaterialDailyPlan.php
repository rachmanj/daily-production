<?php

namespace App\Models;

use App\Enums\MaterialType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialDailyPlan extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'material_type',
        'year',
        'month',
        'daily_plan_tonnage',
        'monthly_plan_tonnage',
        'operating_hours_per_day',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'material_type' => MaterialType::class,
            'year' => 'integer',
            'month' => 'integer',
            'daily_plan_tonnage' => 'decimal:2',
            'monthly_plan_tonnage' => 'decimal:2',
            'operating_hours_per_day' => 'decimal:2',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
