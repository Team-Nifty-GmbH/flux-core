<?php

namespace FluxErp\Actions\ProductConfigurationRule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\ProductConfigurationRule;
use FluxErp\Rulesets\ProductConfigurationRule\CreateProductConfigurationRuleRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateProductConfigurationRule extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationRule::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductConfigurationRuleRuleset::class;
    }

    public function performAction(): ProductConfigurationRule
    {
        $targetItems = Arr::pull($this->data, 'target_items') ?? [];

        $productConfigurationRule = app(ProductConfigurationRule::class, ['attributes' => $this->data]);
        $productConfigurationRule->save();

        if (! $productConfigurationRule->target_group_id) {
            $productConfigurationRule->targetItems()->sync($targetItems);
        }

        return $productConfigurationRule->refresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $item = resolve_static(ProductConfigurationGroupItem::class, 'query')
            ->whereKey($this->getData('product_configuration_group_item_id'))
            ->first(['id', 'product_configuration_group_id']);

        if ((int) $this->getData('target_group_id') === (int) $item->product_configuration_group_id
            || in_array((int) $item->getKey(), array_map('intval', $this->getData('target_items') ?? []), true)
        ) {
            throw ValidationException::withMessages([
                'target_group_id' => [__('An option cannot point at itself or its own group.')],
            ])
                ->errorBag('createProductConfigurationRule');
        }
    }
}
