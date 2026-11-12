<?php

namespace FluxErp\Rulesets\ProductConfigurationGroup;

use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateProductConfigurationGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductConfigurationGroup::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:product_configuration_groups,uuid',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selection_type_enum' => [
                'required',
                Rule::enum(ConfigurationSelectionTypeEnum::class),
            ],
            'price_mode_enum' => [
                'required',
                Rule::enum(ConfigurationPriceModeEnum::class),
            ],
            'min_selections' => 'nullable|integer|min:0',
            'max_selections' => 'nullable|integer|min:1',
        ];
    }
}
