<?php

namespace FluxErp\Rulesets\ProductConfigurationRule;

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\ProductConfigurationRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateProductConfigurationRuleRuleset extends FluxRuleset
{
    protected static ?string $model = ProductConfigurationRule::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationRule::class]),
            ],
            'rule_type_enum' => [
                'sometimes',
                'required',
                Rule::enum(ConfigurationRuleTypeEnum::class),
            ],
            'target_group_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroup::class]),
            ],
            'target_items' => [
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
