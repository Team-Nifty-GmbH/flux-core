<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductOption\CreateProductOption;
use FluxErp\Actions\ProductOption\DeleteProductOption;
use FluxErp\Actions\ProductOption\UpdateProductOption;
use FluxErp\Helpers\Helper;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rulesets\ProductOptionGroup\UpdateProductOptionGroupRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateProductOptionGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProductOptionGroupRuleset::class;
    }

    public function performAction(): Model
    {
        $productOptions = Arr::pull($this->data, 'product_options');

        $productOptionGroup = resolve_static(ProductOptionGroup::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

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
}
