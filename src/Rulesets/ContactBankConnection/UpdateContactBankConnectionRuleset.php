<?php

namespace FluxErp\Rulesets\ContactBankConnection;

use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateContactBankConnectionRuleset extends FluxRuleset
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
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ContactBankConnection::class]),
            ],
            'contact_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'iban' => ['string', app(Iban::class)],
        ];
    }
}
