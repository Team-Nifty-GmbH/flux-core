<?php

namespace FluxErp\Rulesets\OrderType;

use FluxErp\Models\Client;
use FluxErp\Models\EmailTemplate;
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
            'name' => 'sometimes|required|string|max:255',
            'description' => 'string|nullable',
            'email_template_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => EmailTemplate::class]),
            ],
            'print_layouts' => 'array|nullable',
            'print_layouts.*' => 'required|string',
            'post_stock_print_layouts' => 'array|nullable',
            'post_stock_print_layouts.*' => 'required|string',
            'reserve_stock_print_layouts' => 'array|nullable',
            'reserve_stock_print_layouts.*' => 'required|string',
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
            'is_visible_in_sidebar' => 'boolean',
        ];
    }
}
