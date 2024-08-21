<?php

namespace FluxErp\Rulesets\ProductCrossSelling;

use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductCrossSellingRuleset extends FluxRuleset
{
    protected static ?string $model = ProductCrossSelling::class;

    protected static bool $addAdditionalColumnRules = false;

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
