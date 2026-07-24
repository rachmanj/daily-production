<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function pits(): HasMany
    {
        return $this->hasMany(Pit::class);
    }

    public function equipmentAssignments(): HasMany
    {
        return $this->hasMany(EquipmentAssignment::class);
    }

    public function projectSiteMappings(): HasMany
    {
        return $this->hasMany(ProjectSiteMapping::class);
    }

    public function dailyEntries(): HasMany
    {
        return $this->hasMany(DailyEntry::class);
    }

    public function fuelReceipts(): HasMany
    {
        return $this->hasMany(FuelReceipt::class);
    }

    public function fuelStockMovements(): HasMany
    {
        return $this->hasMany(FuelStockMovement::class);
    }

    public function monthlyPlans(): HasMany
    {
        return $this->hasMany(MonthlyPlan::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
