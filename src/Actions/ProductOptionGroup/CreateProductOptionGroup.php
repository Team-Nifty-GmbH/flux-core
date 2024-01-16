<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateProductOptionGroup extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateProductOptionGroupRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function performAction(): ProductOptionGroup
    {
        $productOptions = Arr::pull($this->data, 'product_options');

        $productOptionGroup = new ProductOptionGroup($this->data);
        $productOptionGroup->save();

        if (! is_null($productOptions)) {
            $productOptionGroup->productOptions()->createMany($productOptions);
        }

        return $productOptionGroup->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOptionGroup());

        $this->data = $validator->validate();
    }
}
