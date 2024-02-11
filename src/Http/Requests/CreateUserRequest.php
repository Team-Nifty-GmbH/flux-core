<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Language;
use FluxErp\Models\MailAccount;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:users,uuid',
            'language_id' => [
                'nullable',
                'integer',
                new ModelExists(Language::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(User::class),
            ],
            'email' => 'required|email|unique:users,email',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'user_code' => 'required|string|unique:users,user_code',
            'is_active' => 'sometimes|boolean',
            'mail_accounts' => 'array',
            'mail_accounts.*' => [
                'required',
                'integer',
                new ModelExists(MailAccount::class),
            ],
        ];
    }
}
