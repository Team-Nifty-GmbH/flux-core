<?php

namespace FluxErp\Rulesets\OrderType;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\OrderType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateOrderTypeRuleset extends FluxRuleset
{
    protected static ?string $model = OrderType::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:order_types,uuid',
            'client_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'name' => 'required|string',
            'description' => 'string|nullable',
            'mail_subject' => 'string|nullable',
            'mail_body' => 'string|nullable',
            'print_layouts' => 'array|nullable',
            'print_layouts.*' => 'required|string',
            'order_type_enum' => [
                'required',
                Rule::enum(OrderTypeEnum::class),
            ],
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }
}
