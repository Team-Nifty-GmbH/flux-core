<?php

namespace FluxErp\Rulesets\ContactBankConnection;

use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateContactBankConnectionRuleset extends FluxRuleset
{
    protected static ?string $model = ContactBankConnection::class;

    public static function getRules(): array
    {
        return array_merge(
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            parent::getRules()
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:bank_connections,uuid',
            'contact_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'iban' => ['required', 'string', app(Iban::class)],
        ];
    }
}
