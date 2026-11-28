<?php

namespace FluxErp\Actions\Product\ProductProductConfigurationGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Rulesets\Product\ProductProductConfigurationGroup\UpdateProductProductConfigurationGroupRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateProductProductConfigurationGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductProductConfigurationGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProductProductConfigurationGroupRuleset::class;
    }

    public function performAction(): Model
    {
        $productProductConfigurationGroup = resolve_static(ProductProductConfigurationGroup::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first();

        $productProductConfigurationGroup->fill($this->data);
        $productProductConfigurationGroup->save();

        return $productProductConfigurationGroup->withoutRelations()->fresh();
    }
}
