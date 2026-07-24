<?php

namespace App\Models;

use App\Enums\ShiftName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'name' => ShiftName::class,
        ];
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class);
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class);
    }

    public function equipmentDeployments(): HasMany
    {
        return $this->hasMany(EquipmentDeployment::class);
    }
}
