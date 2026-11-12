<?php

namespace FluxErp\Rulesets\ProductConfigurationGroup;

use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateProductConfigurationGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductConfigurationGroup::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroup::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'selection_type_enum' => [
                'sometimes',
                'required',
                Rule::enum(ConfigurationSelectionTypeEnum::class),
            ],
            'price_mode_enum' => [
                'sometimes',
                'required',
                Rule::enum(ConfigurationPriceModeEnum::class),
            ],
            'min_selections' => 'nullable|integer|min:0',
            'max_selections' => 'nullable|integer|min:1',
        ];
    }
}
