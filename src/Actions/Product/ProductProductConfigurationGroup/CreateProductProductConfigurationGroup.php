<?php

namespace FluxErp\Actions\Product\ProductProductConfigurationGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Rulesets\Product\ProductProductConfigurationGroup\CreateProductProductConfigurationGroupRuleset;
use Illuminate\Validation\Rule;

class CreateProductProductConfigurationGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductProductConfigurationGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductProductConfigurationGroupRuleset::class;
    }

    public function performAction(): ProductProductConfigurationGroup
    {
        $productProductConfigurationGroup = app(
            ProductProductConfigurationGroup::class,
            ['attributes' => $this->data]
        );
        $productProductConfigurationGroup->save();

        return $productProductConfigurationGroup->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['is_required'] ??= false;
        $this->data['sort'] ??= 0;

        $this->addRules([
            'product_configuration_group_id' => Rule::unique(
                'product_product_configuration_group',
                'product_configuration_group_id'
            )
                ->where('product_id', $this->getData('product_id') ?? 0),
        ]);
    }
}
