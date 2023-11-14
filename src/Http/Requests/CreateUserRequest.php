<?php

namespace FluxErp\Http\Requests;

use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:users,uuid',
            'language_id' => 'sometimes|integer|exists:languages,id,deleted_at,NULL',
            'parent_id' => 'integer|nullable|exists:users,id,deleted_at,NULL',
            'email' => 'required|email|unique:users,email',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'user_code' => 'required|string|unique:users,user_code',
            'is_active' => 'sometimes|boolean',
            'mail_accounts' => 'sometimes|required|array',
            'mail_accounts.*' => 'integer|exists:mail_accounts,id',
        ];
    }
}
