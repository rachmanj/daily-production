<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('equipment.assign') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'equipment' => ['required', 'array', 'min:1'],
            'equipment.*.equipment_id' => ['required', 'integer'],
            'equipment.*.unit_code' => ['required', 'string', 'max:50'],
            'equipment.*.description' => ['nullable', 'string', 'max:255'],
            'equipment.*.plant_type_name' => ['nullable', 'string', 'max:100'],
            'equipment.*.project_code' => ['required', 'string', 'max:20'],
            'pit_id' => ['required', 'exists:pits,id'],
            'site_id' => ['required', 'exists:sites,id'],
        ];
    }
}
