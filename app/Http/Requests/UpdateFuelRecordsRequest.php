<?php

namespace App\Http\Requests;

use App\Enums\FuelUsageCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFuelRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('dailyEntry')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'records' => ['required', 'array'],
            'records.*.equipment_id' => ['required', 'integer'],
            'records.*.unit_code' => ['required', 'string', 'max:50'],
            'records.*.shift_id' => ['required', 'exists:shifts,id'],
            'records.*.fuel_type_id' => ['nullable', 'exists:fuel_types,id'],
            'records.*.liters' => ['required', 'numeric', 'min:0'],
            'records.*.working_hours' => ['nullable', 'numeric', 'min:0'],
            'records.*.usage_category' => ['required', Rule::enum(FuelUsageCategory::class)],
        ];
    }
}
