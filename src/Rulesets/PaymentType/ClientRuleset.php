<?php

namespace FluxErp\Rulesets\PaymentType;

use FluxErp\Models\Client;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ClientRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'clients' => 'sometimes|required|array',
            'clients.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
        ];
    }
}
