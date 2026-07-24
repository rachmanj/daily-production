<?php

namespace App\Http\Requests;

use App\Enums\PitOwner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pit = $this->route('pit');

        return [
            'site_id' => ['required', 'exists:sites,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pits', 'code')
                    ->where('site_id', $this->input('site_id'))
                    ->ignore($pit),
            ],
            'owner' => ['required', Rule::enum(PitOwner::class)],
            'is_active' => ['boolean'],
        ];
    }
}
