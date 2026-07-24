<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentAssignment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'equipment_id',
        'unit_code',
        'description',
        'plant_type_name',
        'project_code',
        'site_id',
        'pit_id',
        'is_active_for_tracking',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active_for_tracking' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pit(): BelongsTo
    {
        return $this->belongsTo(Pit::class);
    }
}
