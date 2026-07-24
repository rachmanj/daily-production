<?php

namespace App\Models;

use App\Enums\PitOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pit extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'code',
        'owner',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'owner' => PitOwner::class,
            'is_active' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function equipmentAssignments(): HasMany
    {
        return $this->hasMany(EquipmentAssignment::class);
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class);
    }

    public function equipmentDeployments(): HasMany
    {
        return $this->hasMany(EquipmentDeployment::class);
    }

    public function planTargets(): HasMany
    {
        return $this->hasMany(PlanTarget::class);
    }
}
