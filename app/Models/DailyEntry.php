<?php

namespace App\Models;

use App\Enums\EntrySource;
use App\Enums\EntryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyEntry extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'production_date',
        'site_id',
        'created_by',
        'approved_by',
        'status',
        'source',
        'source_file',
        'submitted_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'status' => EntryStatus::class,
            'source' => EntrySource::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    public function siteInfo(): HasOne
    {
        return $this->hasOne(SiteInfo::class);
    }
}
