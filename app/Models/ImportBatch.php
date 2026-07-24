<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'original_filename',
        'stored_path',
        'status',
        'parsed_payload',
        'row_errors',
    ];

    protected function casts(): array
    {
        return [
            'parsed_payload' => 'array',
            'row_errors' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
