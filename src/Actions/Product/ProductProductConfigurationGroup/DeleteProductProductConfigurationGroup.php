<?php

namespace FluxErp\Actions\Product\ProductProductConfigurationGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationDefault;
use FluxErp\Rulesets\Product\ProductProductConfigurationGroup\DeleteProductProductConfigurationGroupRuleset;
use Illuminate\Database\Eloquent\Builder;

class DeleteProductProductConfigurationGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductProductConfigurationGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductProductConfigurationGroupRuleset::class;
    }

    public function performAction(): ?bool
    {
        $productProductConfigurationGroup = resolve_static(ProductProductConfigurationGroup::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first();

        resolve_static(ProductConfigurationDefault::class, 'query')
            ->where('product_id', $productProductConfigurationGroup->product_id)
            ->whereHas(
                'productConfigurationGroupItem',
                fn (Builder $query) => $query->where(
                    'product_configuration_group_id',
                    $productProductConfigurationGroup->product_configuration_group_id
                )
            )
            ->delete();

        return $productProductConfigurationGroup->delete();
    }
}
