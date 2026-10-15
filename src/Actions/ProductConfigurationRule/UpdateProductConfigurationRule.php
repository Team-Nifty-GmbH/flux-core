<?php

namespace FluxErp\Actions\ProductConfigurationRule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductConfigurationRule;
use FluxErp\Rulesets\ProductConfigurationRule\UpdateProductConfigurationRuleRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateProductConfigurationRule extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationRule::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProductConfigurationRuleRuleset::class;
    }

    public function performAction(): Model
    {
        $targetItems = Arr::pull($this->data, 'target_items');

        $productConfigurationRule = resolve_static(ProductConfigurationRule::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $productConfigurationRule->fill($this->data);

        if (! is_null($targetItems)) {
            $productConfigurationRule->target_group_id = null;
        }

        $productConfigurationRule->save();

        if ($productConfigurationRule->target_group_id) {
            $productConfigurationRule->targetItems()->detach();
        } elseif (! is_null($targetItems)) {
            $productConfigurationRule->targetItems()->sync($targetItems);
        }

        return $productConfigurationRule->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $rule = resolve_static(ProductConfigurationRule::class, 'query')
            ->whereKey($this->getData('id'))
            ->with('productConfigurationGroupItem:id,product_configuration_group_id')
            ->first();
        $item = $rule->productConfigurationGroupItem;

        if ((array_key_exists('target_group_id', $this->data)
                && $this->getData('target_group_id') === $item->product_configuration_group_id)
            || in_array($item->getKey(), $this->getData('target_items') ?? [])
        ) {
            throw ValidationException::withMessages([
                'target_group_id' => [__('An option cannot point at itself or its own group.')],
            ])
                ->errorBag('updateProductConfigurationRule');
        }
    }
}
