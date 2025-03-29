<?php

namespace FluxErp\Rulesets\ContactBankConnection;

use FluxErp\Models\ContactBankConnection;
use FluxErp\Rules\Iban;
use FluxErp\Rulesets\FluxRuleset;

class BankConnectionRuleset extends FluxRuleset
{
    protected static ?string $model = ContactBankConnection::class;

    public function rules(): array
    {
        return [
            'iban' => [
                'nullable',
                'string',
                app(Iban::class),
            ],
            'account_holder' => 'string|nullable|max:255',
            'bank_name' => 'string|nullable|max:255',
            'bic' => 'string|nullable|max:255',
        ];
    }
}
