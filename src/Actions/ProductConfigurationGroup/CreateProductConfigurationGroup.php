<?php

namespace FluxErp\Actions\ProductConfigurationGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Rulesets\ProductConfigurationGroup\CreateProductConfigurationGroupRuleset;
use Illuminate\Validation\ValidationException;

class CreateProductConfigurationGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductConfigurationGroupRuleset::class;
    }

    public function performAction(): ProductConfigurationGroup
    {
        $productConfigurationGroup = app(ProductConfigurationGroup::class, ['attributes' => $this->data]);
        $productConfigurationGroup->save();

        return $productConfigurationGroup->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['selection_type_enum'] ??= ConfigurationSelectionTypeEnum::Single->value;
        $this->data['price_mode_enum'] ??= ConfigurationPriceModeEnum::Surcharge->value;

        if ($this->getData('selection_type_enum') === ConfigurationSelectionTypeEnum::Single->value) {
            $this->data['min_selections'] = null;
            $this->data['max_selections'] = null;
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (! is_null($this->getData('min_selections'))
            && ! is_null($this->getData('max_selections'))
            && bccomp($this->getData('min_selections'), $this->getData('max_selections')) === 1
        ) {
            throw ValidationException::withMessages([
                'min_selections' => [__('The minimum must not exceed the maximum.')],
            ])
                ->errorBag('createProductConfigurationGroup');
        }
    }
}
