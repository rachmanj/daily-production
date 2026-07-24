<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExcelImportRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'type' => ['nullable', 'in:dpr,info,fuel'],
        ];
    }
}
