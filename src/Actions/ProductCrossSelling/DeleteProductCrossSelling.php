<?php

namespace FluxErp\Actions\ProductCrossSelling;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rulesets\ProductCrossSelling\DeleteProductCrossSellingRuleset;

class DeleteProductCrossSelling extends FluxAction
{
    public static function models(): array
    {
        return [ProductCrossSelling::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductCrossSellingRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(ProductCrossSelling::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
