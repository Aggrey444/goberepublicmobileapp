<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['SUPER_ADMIN', 'ADMIN', 'STAFF'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
