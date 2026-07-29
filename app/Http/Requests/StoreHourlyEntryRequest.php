<?php

namespace App\Http\Requests;

use App\Enums\MaterialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHourlyEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('entry.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'production_date' => ['required', 'date'],
            'site_id' => ['required', 'exists:sites,id'],
            'material_type' => ['required', Rule::enum(MaterialType::class)],
            'shift_id' => ['required', 'exists:shifts,id'],
        ];
    }
}
