<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductOption;
use FluxErp\Rulesets\ProductOption\CreateProductOptionRuleset;
use Illuminate\Support\Facades\Validator;

class CreateProductOption extends FluxAction
{
    public static function models(): array
    {
        return [ProductOption::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductOptionRuleset::class;
    }

    public function performAction(): ProductOption
    {
        $productOption = app(ProductOption::class, ['attributes' => $this->data]);
        $productOption->save();

        return $productOption->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(ProductOption::class));

        $this->data = $validator->validate();
    }
}
