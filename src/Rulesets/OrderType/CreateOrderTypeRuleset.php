<?php

namespace FluxErp\Rulesets\OrderType;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\OrderType;
use FluxErp\Models\Tenant;
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
            'tenant_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
            'name' => 'required|string|max:255',
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
            'order_type_enum' => [
                'required',
                Rule::enum(OrderTypeEnum::class),
            ],
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
            'is_visible_in_sidebar' => 'boolean',
        ];
    }
}
