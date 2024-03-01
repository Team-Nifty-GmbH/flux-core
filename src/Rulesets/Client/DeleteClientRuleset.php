<?php

namespace FluxErp\Rulesets\Client;

use FluxErp\Models\Client;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteClientRuleset extends FluxRuleset
{
    protected static ?string $model = Client::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}
