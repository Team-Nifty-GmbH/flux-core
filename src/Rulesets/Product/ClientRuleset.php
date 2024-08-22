<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Client;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ClientRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'clients' => 'array',
            'clients.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
        ];
    }
}
