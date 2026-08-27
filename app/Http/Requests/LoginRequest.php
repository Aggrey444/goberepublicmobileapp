<?php

namespace App\Http\Requests;

class LoginRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required_without:phone', 'string', 'email'],
            'phone' => ['required_without:email', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
