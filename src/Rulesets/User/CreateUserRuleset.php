<?php

namespace FluxErp\Rulesets\User;

use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rules\Password;

class CreateUserRuleset extends FluxRuleset
{
    protected static ?string $model = User::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:users,uuid',
            'language_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'email' => 'required|email|unique:users,email',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'user_code' => 'required|string|unique:users,user_code',
            'cost_per_hour' => [
                'nullable',
                app(Numeric::class),
            ],
            'is_active' => 'sometimes|boolean',
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
