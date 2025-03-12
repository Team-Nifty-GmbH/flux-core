<?php

namespace FluxErp\Rulesets\ProductCrossSelling;

use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductCrossSellingRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = ProductCrossSelling::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductCrossSelling::class]),
            ],
        ];
    }
}
