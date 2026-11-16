<?php

namespace FluxErp\Rulesets\Product\ProductProductConfigurationGroup;

use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductProductConfigurationGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductProductConfigurationGroup::class;

    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductProductConfigurationGroup::class]),
            ],
            'is_required' => 'sometimes|required|boolean',
            'sort' => 'sometimes|required|integer|min:0',
        ];
    }
}
