<?php

namespace FluxErp\Actions\ProductConfigurationGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Rulesets\ProductConfigurationGroup\UpdateProductConfigurationGroupRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateProductConfigurationGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProductConfigurationGroupRuleset::class;
    }

    public function performAction(): Model
    {
        $productConfigurationGroup = resolve_static(ProductConfigurationGroup::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $productConfigurationGroup->fill($this->data);
        $productConfigurationGroup->save();

        return $productConfigurationGroup->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $stored = resolve_static(ProductConfigurationGroup::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'selection_type_enum']);

        $selectionType = $this->getData('selection_type_enum') ?? $stored?->selection_type_enum?->value;

        if ($selectionType === ConfigurationSelectionTypeEnum::Single->value) {
            $this->data['min_selections'] = null;
            $this->data['max_selections'] = null;
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $stored = resolve_static(ProductConfigurationGroup::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'min_selections', 'max_selections']);

        $min = array_key_exists('min_selections', $this->data)
            ? $this->getData('min_selections')
            : $stored->min_selections;
        $max = array_key_exists('max_selections', $this->data)
            ? $this->getData('max_selections')
            : $stored->max_selections;

        if (! is_null($min) && ! is_null($max) && bccomp($min, $max) === 1) {
            throw ValidationException::withMessages([
                'min_selections' => [__('The minimum must not exceed the maximum.')],
            ])
                ->errorBag('updateProductConfigurationGroup');
        }
    }
}
