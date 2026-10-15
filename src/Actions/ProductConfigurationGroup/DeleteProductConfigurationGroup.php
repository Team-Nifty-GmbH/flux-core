<?php

namespace FluxErp\Actions\ProductConfigurationGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Rulesets\ProductConfigurationGroup\DeleteProductConfigurationGroupRuleset;
use Illuminate\Validation\ValidationException;

class DeleteProductConfigurationGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductConfigurationGroupRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(ProductConfigurationGroup::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(ProductProductConfigurationGroup::class, 'query')
            ->where('product_configuration_group_id', $this->getData('id'))
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'id' => [__('Configuration groups attached to products cannot be deleted.')],
            ])
                ->status(423)
                ->errorBag('deleteProductConfigurationGroup');
        }
    }
}
