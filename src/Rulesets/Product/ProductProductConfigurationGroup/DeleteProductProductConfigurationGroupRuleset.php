<?php

namespace FluxErp\Rulesets\Product\ProductProductConfigurationGroup;

use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductProductConfigurationGroupRuleset extends FluxRuleset
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
        ];
    }
}
