<?php

namespace FluxErp\Rulesets\Client;

use FluxErp\Models\BankConnection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class BankConnectionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'bank_connections' => 'array|nullable',
            'bank_connections.*' => [
                'integer',
                new ModelExists(BankConnection::class),
            ],
        ];
    }
}
