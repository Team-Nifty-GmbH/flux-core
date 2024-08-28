<?php

namespace FluxErp\Rulesets\BankConnection;

use FluxErp\Models\BankConnection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteBankConnectionRuleset extends FluxRuleset
{
    protected static ?string $model = BankConnection::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => BankConnection::class]),
            ],
        ];
    }
}
