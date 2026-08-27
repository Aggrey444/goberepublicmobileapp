<?php

namespace App\Http\Requests;

class AddToCartRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
