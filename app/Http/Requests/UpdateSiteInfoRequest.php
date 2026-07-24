<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteInfoRequest extends FormRequest
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
            'weather' => ['nullable', 'string', 'max:100'],
            'rain_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'slippery_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'manpower_plan' => ['nullable', 'integer', 'min:0'],
            'manpower_actual' => ['nullable', 'integer', 'min:0'],
            'safety_notes' => ['nullable', 'string', 'max:2000'],
            'fuel_stock_liters' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
