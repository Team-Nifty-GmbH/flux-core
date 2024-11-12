<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductOption\CreateProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rulesets\ProductOptionGroup\CreateProductOptionGroupRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateProductOptionGroup extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateProductOptionGroupRuleset::class;
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function performAction(): ProductOptionGroup
    {
        $productOptions = Arr::pull($this->data, 'product_options', []);

        $productOptionGroup = app(ProductOptionGroup::class, ['attributes' => $this->data]);
        $productOptionGroup->save();

        foreach ($productOptions as $productOption) {
            $productOption = array_merge($productOption, ['product_option_group_id' => $productOptionGroup->id]);
            CreateProductOption::make($productOption)
                ->validate()
                ->execute();
        }

        return $productOptionGroup->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(ProductOptionGroup::class));

        $this->data = $validator->validate();
    }
}
