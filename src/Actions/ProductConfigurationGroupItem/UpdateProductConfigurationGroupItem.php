<?php

namespace FluxErp\Actions\ProductConfigurationGroupItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Rulesets\ProductConfigurationGroupItem\UpdateProductConfigurationGroupItemRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class UpdateProductConfigurationGroupItem extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationGroupItem::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProductConfigurationGroupItemRuleset::class;
    }

    public function performAction(): Model
    {
        $productConfigurationGroupItem = resolve_static(ProductConfigurationGroupItem::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $productConfigurationGroupItem->fill($this->data);
        $productConfigurationGroupItem->save();

        return $productConfigurationGroupItem->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->addRules([
            'product_id' => Rule::unique('product_configuration_group_items', 'product_id')
                ->where(
                    'product_configuration_group_id',
                    resolve_static(ProductConfigurationGroupItem::class, 'query')
                        ->whereKey($this->getData('id'))
                        ->value('product_configuration_group_id') ?? 0
                )
                ->ignore($this->getData('id') ?? 0),
        ]);
    }
}
