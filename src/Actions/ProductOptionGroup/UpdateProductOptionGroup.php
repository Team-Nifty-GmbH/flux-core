<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductOption\CreateProductOption;
use FluxErp\Actions\ProductOption\DeleteProductOption;
use FluxErp\Actions\ProductOption\UpdateProductOption;
use FluxErp\Http\Requests\UpdateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateProductOptionGroup extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProductOptionGroupRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function performAction(): Model
    {
        $productOptionGroup = ProductOptionGroup::query()
            ->whereKey($this->data['id'])
            ->first();

        $productOptions = Arr::pull($this->data, 'product_options');

        $productOptionGroup->fill($this->data);
        $productOptionGroup->save();

        if (! is_null($productOptions)) {
            $existingProductOptions = $productOptionGroup->productOptions()->pluck('id')->toArray();
            $productOptionGroup->productOptions()->whereNotIn('id', Arr::pluck($productOptions, 'id'))->delete();

            $updatedProductOptions = [];
            foreach ($productOptions as $productOption) {
                $productOption = array_merge($productOption, ['product_option_group_id' => $productOptionGroup->id]);
                if (! ($productOption['id'] ?? false)) {
                    try {
                        CreateProductOption::make($productOption)->validate()->execute();
                    } catch (ValidationException) {
                    }
                } else {
                    try {
                        UpdateProductOption::make($productOption)->validate()->execute();
                    } catch (ValidationException) {
                    }
                    $updatedProductOptions[] = $productOption['id'];
                }
            }

            foreach (array_diff($existingProductOptions, $updatedProductOptions) as $deletedProductOption) {
                try {
                    DeleteProductOption::make(['id' => $deletedProductOption])->validate()->execute();
                } catch (ValidationException) {
                }
            }
        }

        return $productOptionGroup->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOptionGroup());

        $this->data = $validator->validate();
    }
}
