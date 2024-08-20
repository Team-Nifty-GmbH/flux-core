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
                app(ModelExists::class, ['model' => SepaMandate::class]),
            ],
            'contact_bank_connection_id' => [
                'integer',
                app(ModelExists::class, ['model' => ContactBankConnection::class]),
            ],
            'signed_date' => 'date|nullable',
        ];
    }
}
