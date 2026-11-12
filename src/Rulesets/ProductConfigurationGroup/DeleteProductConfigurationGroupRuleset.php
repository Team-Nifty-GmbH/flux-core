<?php

namespace FluxErp\Rulesets\ProductConfigurationGroup;

use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductConfigurationGroupRuleset extends FluxRuleset
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
        ];
    }
}
