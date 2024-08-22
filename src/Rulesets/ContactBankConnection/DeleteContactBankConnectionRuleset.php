<?php

namespace FluxErp\Rulesets\ContactBankConnection;

use FluxErp\Models\ContactBankConnection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteContactBankConnectionRuleset extends FluxRuleset
{
    protected static ?string $model = ContactBankConnection::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ContactBankConnection::class]),
            ],
        ];
    }
}
