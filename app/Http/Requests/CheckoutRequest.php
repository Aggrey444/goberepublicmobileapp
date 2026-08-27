<?php

namespace App\Http\Requests;

class CheckoutRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'delivery_information' => ['nullable', 'array'],
            'delivery_information.recipient_name' => ['required_with:delivery_information', 'string', 'max:255'],
            'delivery_information.phone' => ['required_with:delivery_information', 'string', 'max:30'],
            'delivery_information.address' => ['required_with:delivery_information', 'string', 'max:255'],
            'delivery_information.city' => ['nullable', 'string', 'max:255'],
            'delivery_information.additional_notes' => ['nullable', 'string'],

            // Backwards-compatible flat payload.
            'recipient_name' => ['required_without:delivery_information', 'string', 'max:255'],
            'phone' => ['required_without:delivery_information', 'string', 'max:30'],
            'address' => ['required_without:delivery_information', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'additional_notes' => ['nullable', 'string'],
        ];
    }
}
