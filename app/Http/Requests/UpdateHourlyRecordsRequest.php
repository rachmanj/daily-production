<?php

namespace App\Http\Requests;

use App\Enums\MaterialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHourlyRecordsRequest extends FormRequest
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
            'material_type' => ['required', Rule::enum(MaterialType::class)],
            'shift_id' => ['required', 'exists:shifts,id'],
            'records' => ['required', 'array'],
            'records.*.equipment_id' => ['required', 'integer'],
            'records.*.hour_slot' => ['required', 'integer', 'min:0', 'max:23'],
            'records.*.tonnage' => ['nullable', 'numeric', 'min:0'],
            'records.*.unit_code' => ['nullable', 'string', 'max:50'],
            'records.*.location' => ['nullable', 'string', 'max:255'],
            'records.*.loader_info' => ['nullable', 'string', 'max:255'],
            'records.*.pit_id' => ['nullable', 'exists:pits,id'],
        ];
    }
}
