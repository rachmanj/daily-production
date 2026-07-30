<?php

namespace App\Http\Requests;

use App\Enums\MaterialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('equipment.assign') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('material_type') === '') {
            $this->merge(['material_type' => null]);
        }

        if ($this->input('equipment_role') === '') {
            $this->merge(['equipment_role' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'material_type' => ['nullable', Rule::enum(MaterialType::class)],
            'equipment_role' => ['nullable', 'string', 'max:50'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active_for_tracking' => ['required', 'boolean'],
        ];
    }
}
