<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mode' => ['nullable', 'in:cart,buy_now'],

            'shipping'           => ['required', 'array'],
            'shipping.name'      => ['required', 'string', 'max:255'],
            'shipping.email'     => ['required', 'email', 'max:255'],
            'shipping.phone'     => ['required', 'string', 'max:30'],
            'shipping.line1'     => ['required', 'string', 'max:255'],
            'shipping.line2'     => ['nullable', 'string', 'max:255'],
            'shipping.city'      => ['required', 'string', 'max:120'],
            'shipping.state'     => ['required', 'string', 'max:120'],
            'shipping.postcode'  => ['required', 'string', 'max:20'],
            'shipping.country'   => ['required', 'string', 'max:2'],

            'billing_same'       => ['nullable', 'boolean'],
            'billing'            => ['nullable', 'array', 'required_if:billing_same,false'],
            'billing.name'       => ['nullable', 'string', 'max:255'],
            'billing.line1'      => ['nullable', 'string', 'max:255'],
            'billing.city'       => ['nullable', 'string', 'max:120'],
            'billing.state'      => ['nullable', 'string', 'max:120'],
            'billing.postcode'   => ['nullable', 'string', 'max:20'],
            'billing.country'    => ['nullable', 'string', 'max:2'],
        ];
    }
}
