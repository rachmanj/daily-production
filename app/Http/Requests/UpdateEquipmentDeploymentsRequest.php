<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentDeploymentsRequest extends FormRequest
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
            'records.*.pit_id' => ['nullable', 'exists:pits,id'],
            'records.*.shift_id' => ['required', 'exists:shifts,id'],
            'records.*.prod_ob_bcm' => ['nullable', 'numeric', 'min:0'],
            'records.*.prod_coal_ton' => ['nullable', 'numeric', 'min:0'],
            'records.*.operator_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
