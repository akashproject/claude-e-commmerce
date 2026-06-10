<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreVariantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'variants'                         => ['required', 'array', 'min:1'],
            'variants.*.sku'                   => ['required', 'string', 'distinct', 'unique:product_variants,sku'],
            'variants.*.price'                 => ['required', 'numeric', 'min:0'],
            'variants.*.stock'                 => ['nullable', 'integer', 'min:0'],
            'variants.*.image'                 => ['nullable', 'string'],
            'variants.*.attribute_value_ids'   => ['required', 'array', 'min:1'],
            'variants.*.attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
        ];
    }
}
