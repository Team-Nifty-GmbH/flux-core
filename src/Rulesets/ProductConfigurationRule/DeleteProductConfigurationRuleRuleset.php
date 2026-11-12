<?php

namespace FluxErp\Rulesets\ProductConfigurationRule;

use FluxErp\Models\ProductConfigurationRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductConfigurationRuleRuleset extends FluxRuleset
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
        ];
    }
}
