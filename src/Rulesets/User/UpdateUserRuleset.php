<?php

namespace FluxErp\Rulesets\User;

use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rules\Password;

class UpdateUserRuleset extends FluxRuleset
{
    protected static ?string $model = User::class;

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
            'cost_per_hour' => 'nullable|numeric',
            'is_active' => 'sometimes|required|boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            resolve_static(MailAccountRuleset::class, 'getRules')
        );
    }
}
