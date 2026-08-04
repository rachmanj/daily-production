<?php

namespace App\Http\Requests;

use App\Enums\PitOwner;
use App\Enums\PlanMetric;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'targets.*.metric' => ['required', Rule::enum(PlanMetric::class)],
            'targets.*.owner' => ['required', Rule::enum(PitOwner::class)],
            'targets.*.target_value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
