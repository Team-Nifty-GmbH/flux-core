<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Language;
use FluxErp\Models\MailAccount;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
            'language_id' => [
                'integer',
                new ModelExists(Language::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(User::class),
            ],
            'email' => 'sometimes|required|email|unique:users,email',
            'firstname' => 'sometimes|required|string',
            'lastname' => 'sometimes|required|string',
            'password' => ['sometimes', 'required', Password::min(8)->mixedCase()->numbers()],
            'user_code' => 'sometimes|required|string|unique:users,user_code',
            'is_active' => 'sometimes|required|boolean',
            'mail_accounts' => 'array',
            'mail_accounts.*' => [
                'integer',
                new ModelExists(MailAccount::class),
            ],
        ];
    }
}
