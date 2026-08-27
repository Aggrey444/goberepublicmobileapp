<?php

namespace App\Http\Requests;

class InitializePaymentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'email' => ['nullable', 'email'],
        ];
    }
}
