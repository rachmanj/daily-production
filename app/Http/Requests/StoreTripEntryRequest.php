<?php

namespace App\Http\Requests;

use App\Enums\MaterialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTripEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_id' => ['required', 'exists:sites,id'],
            'production_date' => ['required', 'date'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'excavator_id' => ['required', 'integer'],
            'hauler_id' => ['required', 'integer'],
            'material_type' => ['required', Rule::enum(MaterialType::class)],
            'hour_slot' => ['required', 'integer', 'min:0', 'max:23'],
            'volume_bcm' => ['required', 'numeric', 'min:0.01'],
            'load_percent' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'trip_count' => ['nullable', 'numeric', 'min:0.1'],
            'truck_capacity_bcm' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
