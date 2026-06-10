<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreAttributeValueRequest extends FormRequest
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
            'value' => [
                'required', 'string', 'max:120',
                // Unique per attribute (matches the DB composite unique).
                Rule::unique('attribute_values', 'value')
                    ->where('attribute_id', $this->route('attribute')->id),
            ],
            'swatch' => ['nullable', 'string', 'max:255'],
        ];
    }
}
