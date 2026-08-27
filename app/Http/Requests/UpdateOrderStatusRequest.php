<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'order_status' => ['required', Rule::in([
                'PENDING',
                'PAYMENT_PENDING',
                'PAID',
                'PROCESSING',
                'READY',
                'OUT_FOR_DELIVERY',
                'DELIVERED',
                'CANCELLED',
            ])],
        ];
    }
}
