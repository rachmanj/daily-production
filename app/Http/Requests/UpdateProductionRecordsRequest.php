<?php

namespace App\Http\Requests;

use App\Enums\ProductionActivity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductionRecordsRequest extends FormRequest
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
            'records.*.pit_id' => ['required', 'exists:pits,id'],
            'records.*.shift_id' => ['required', 'exists:shifts,id'],
            'records.*.ob_removal_bcm' => ['nullable', 'numeric', 'min:0'],
            'records.*.coal_getting_ton' => ['nullable', 'numeric', 'min:0'],
            'records.*.coal_hauling_ton' => ['nullable', 'numeric', 'min:0'],
            'records.*.activity' => ['nullable', Rule::enum(ProductionActivity::class)],
            'records.*.truck_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
