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
                app(ModelExists::class, ['model' => OrderType::class]),
            ],
            'client_id' => [
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'name' => 'string',
            'description' => 'string|nullable',
            'mail_subject' => 'string|nullable',
            'mail_body' => 'string|nullable',
            'print_layouts' => 'array|nullable',
            'print_layouts.*' => 'required|string',
            'post_stock_print_layouts' => 'array|nullable',
            'post_stock_print_layouts.*' => 'required|string',
            'reserve_stock_print_layouts' => 'array|nullable',
            'reserve_stock_print_layouts.*' => 'required|string',
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }
}
