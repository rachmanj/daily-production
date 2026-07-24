<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('plan.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_id' => ['required', 'exists:sites,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'targets' => ['nullable', 'array'],
            'targets.*.pit_id' => ['required', 'exists:pits,id'],
            'targets.*.metric' => ['required', 'string'],
            'targets.*.owner' => ['required', 'string'],
            'targets.*.target_value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
