<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuelPriceRequest extends FormRequest
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
            'fuel_type_id' => ['required', 'exists:fuel_types,id'],
            'price_per_liter' => ['required', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
        ];
    }
}
