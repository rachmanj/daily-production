<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanTargetsRequest extends FormRequest
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
            'targets' => ['required', 'array'],
            'targets.*.pit_id' => ['required', 'exists:pits,id'],
            'targets.*.metric' => ['required', 'string'],
            'targets.*.owner' => ['required', 'string'],
            'targets.*.target_value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
