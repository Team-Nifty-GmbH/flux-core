<?php

namespace FluxErp\Actions\ProductPropertyGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductProperty\CreateProductProperty;
use FluxErp\Actions\ProductProperty\DeleteProductProperty;
use FluxErp\Actions\ProductProperty\UpdateProductProperty;
use FluxErp\Helpers\Helper;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rulesets\ProductPropertyGroup\UpdateProductPropertyGroupRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateProductPropertyGroup extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateProductPropertyGroupRuleset::class;
    }

    public static function models(): array
    {
        return [ProductPropertyGroup::class];
    }

    public function performAction(): Model
    {
        $productProperties = Arr::pull($this->data, 'product_properties');

        $productPropertyGroup = resolve_static(ProductPropertyGroup::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $productPropertyGroup->fill($this->data);
        $productPropertyGroup->save();

        if (! is_null($productProperties)) {
            Helper::updateRelatedRecords(
                model: $productPropertyGroup,
                related: $productProperties,
                relation: 'productProperties',
                foreignKey: 'product_property_group_id',
                createAction: CreateProductProperty::class,
                updateAction: UpdateProductProperty::class,
                deleteAction: DeleteProductProperty::class
            );
        }

        return $productPropertyGroup->withoutRelations()->fresh();
    }
}
