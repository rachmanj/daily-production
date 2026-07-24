<?php

namespace App\Http\Requests;

use App\Enums\ShiftName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
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
        return [
            'name' => ['required', Rule::enum(ShiftName::class), Rule::unique('shifts', 'name')->ignore($this->route('shift'))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ];
    }
}
