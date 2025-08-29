<?php

namespace FluxErp\Rulesets\User;

use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rules\Password;

class UpdateUserRuleset extends FluxRuleset
{
    protected static ?string $model = User::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            resolve_static(MailAccountRuleset::class, 'getRules'),
            resolve_static(PrinterRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'language_id' => [
                'integer',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'email' => 'sometimes|required|email|max:255|unique:users,email',
            'firstname' => 'sometimes|required|string|max:255',
            'lastname' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'password' => [
                'nullable',
                'max:255',
                Password::min(8)->mixedCase()->numbers(),
            ],
            'user_code' => 'sometimes|required|string|max:255|unique:users,user_code',
            'timezone' => [
                'nullable',
                'timezone',
            ],
            'color' => 'nullable|hex_color',
            'cost_per_hour' => [
                'nullable',
                app(Numeric::class),
            ],
            'is_active' => 'sometimes|required|boolean',
        ];
    }
}
