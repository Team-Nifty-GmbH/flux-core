<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationDefault;
use FluxErp\Rulesets\Product\SyncProductConfigurationDefaultsRuleset;
use FluxErp\Support\ProductConfiguration\ConfigurationValidator;

class SyncProductConfigurationDefaults extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationDefault::class];
    }

    protected function getRulesets(): string|array
    {
        return SyncProductConfigurationDefaultsRuleset::class;
    }

    public function performAction(): array
    {
        $product = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('product_id'))
            ->first();

        $product->productConfigurationDefaults()->delete();

        foreach ($this->getData('defaults') as $default) {
            $product->productConfigurationDefaults()->create($default);
        }

        return $product->defaultConfiguration();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->data['defaults'] = ConfigurationValidator::make(
            resolve_static(Product::class, 'query')
                ->whereKey($this->getData('product_id'))
                ->first()
        )
            ->validate($this->getData('defaults'), isDefault: true);
    }
}
