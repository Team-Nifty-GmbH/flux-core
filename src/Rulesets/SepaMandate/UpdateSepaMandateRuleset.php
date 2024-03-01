<?php

namespace FluxErp\Rulesets\SepaMandate;

use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateSepaMandateRuleset extends FluxRuleset
{
    protected static ?string $model = SepaMandate::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(SepaMandate::class),
            ],
            'contact_bank_connection_id' => [
                'integer',
                new ModelExists(ContactBankConnection::class),
            ],
            'signed_date' => 'date|nullable',
        ];
    }
}
