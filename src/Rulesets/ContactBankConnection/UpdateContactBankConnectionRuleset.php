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

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ContactBankConnection::class),
            ],
            'contact_id' => [
                'integer',
                'nullable',
                new ModelExists(Contact::class),
            ],
            'iban' => ['string', new Iban()],
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            parent::getRules()
        );
    }
}
