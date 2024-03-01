<?php

namespace FluxErp\Rulesets\OrderType;

use FluxErp\Models\Client;
use FluxErp\Models\OrderType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateOrderTypeRuleset extends FluxRuleset
{
    protected static ?string $model = OrderType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(OrderType::class),
            ],
            'client_id' => [
                'integer',
                new ModelExists(Client::class),
            ],
            'name' => 'string',
            'description' => 'string|nullable',
            'mail_subject' => 'string|nullable',
            'mail_body' => 'string|nullable',
            'print_layouts' => 'array|nullable',
            'print_layouts.*' => 'required|string',
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }
}
