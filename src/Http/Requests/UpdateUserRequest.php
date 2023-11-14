<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:users,id,deleted_at,NULL',
            'language_id' => [
                'integer',
                (new ExistsWithIgnore('languages', 'id'))->whereNull('deleted_at'),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('users', 'id'))->whereNull('deleted_at'),
            ],
            'email' => 'sometimes|required|email|unique:users,email',
            'firstname' => 'sometimes|required|string',
            'lastname' => 'sometimes|required|string',
            'password' => ['sometimes', 'required', Password::min(8)->mixedCase()->numbers()],
            'user_code' => 'sometimes|required|string|unique:users,user_code',
            'is_active' => 'sometimes|required|boolean',
            'mail_accounts' => 'sometimes|required|array',
            'mail_accounts.*' => 'integer|exists:mail_accounts,id',
        ];
    }
}
