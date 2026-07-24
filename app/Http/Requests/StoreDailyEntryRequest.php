<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('entry.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'production_date' => ['required', 'date'],
            'site_id' => ['required', 'exists:sites,id'],
            'uuid' => ['nullable', 'uuid', Rule::unique('daily_entries', 'uuid')],
        ];
    }
}
