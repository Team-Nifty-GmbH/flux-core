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
            'clients' => 'required|array',
            'clients.*' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}
