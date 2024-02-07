<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductOption\CreateProductOption;
use FluxErp\Actions\ProductOption\DeleteProductOption;
use FluxErp\Actions\ProductOption\UpdateProductOption;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

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
            Helper::updateRelatedRecords(
                model: $productOptionGroup,
                related: $productOptions,
                relation: 'productOptions',
                foreignKey: 'product_option_group_id',
                createAction: CreateProductOption::class,
                updateAction: UpdateProductOption::class,
                deleteAction: DeleteProductOption::class
            );
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
