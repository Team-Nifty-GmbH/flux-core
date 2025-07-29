<?php

namespace FluxErp\Rulesets\ContactBankConnection;

use FluxErp\Models\ContactBankConnection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CalculateContactBankConnectionBalanceRuleset extends FluxRuleset
{
    protected static ?string $model = ContactBankConnection::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ContactBankConnection::class])
                    ->where('is_credit_account', true),
            ],
        ];
    }
}
