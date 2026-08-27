<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends BaseRequest
{
    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'role' => ['sometimes', Rule::in(['SUPER_ADMIN', 'ADMIN', 'STAFF'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }
}
