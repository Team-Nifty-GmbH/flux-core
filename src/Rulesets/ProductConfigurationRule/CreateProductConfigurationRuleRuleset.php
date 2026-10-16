<?php

namespace FluxErp\Rulesets\ProductConfigurationRule;

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\ProductConfigurationRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateProductConfigurationRuleRuleset extends FluxRuleset
{
    protected static ?string $model = ProductConfigurationRule::class;

    public function rules(): array
    {
        return [
            'product_configuration_group_item_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroupItem::class]),
            ],
            'rule_type_enum' => [
                'required',
                Rule::enum(ConfigurationRuleTypeEnum::class),
            ],
            'target_group_id' => [
                'required_without:target_items',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroup::class]),
            ],
            'target_items' => [
                'required_without:target_group_id',
                'prohibits:target_group_id',
                'nullable',
                'array',
            ],
            'target_items.*' => [
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroupItem::class]),
            ],
        ];
    }
}
