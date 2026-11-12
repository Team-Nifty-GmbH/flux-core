<?php

namespace FluxErp\Actions\ProductConfigurationGroupItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Rulesets\ProductConfigurationGroupItem\CreateProductConfigurationGroupItemRuleset;
use Illuminate\Validation\Rule;

class CreateProductConfigurationGroupItem extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationGroupItem::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductConfigurationGroupItemRuleset::class;
    }

    public function performAction(): ProductConfigurationGroupItem
    {
        $productConfigurationGroupItem = app(ProductConfigurationGroupItem::class, ['attributes' => $this->data]);
        $productConfigurationGroupItem->save();

        return $productConfigurationGroupItem->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['max_amount'] ??= 1;
        $this->data['sort'] ??= 0;

        $this->addRules([
            'product_id' => Rule::unique('product_configuration_group_items', 'product_id')
                ->where('product_configuration_group_id', $this->getData('product_configuration_group_id') ?? 0),
        ]);
    }
}
