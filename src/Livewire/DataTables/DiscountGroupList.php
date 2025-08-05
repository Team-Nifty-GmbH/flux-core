<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\DiscountGroup;

class DiscountGroupList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'is_active',
    ];

    protected string $model = DiscountGroup::class;

    public function getDiscounts(DiscountGroup $discountGroup): array
    {
        $this->skipRender();

        $discounts = $discountGroup->discounts()
            ->with('model')
            ->get()
            ->map(function ($discount) {
                $discount->model_type = __(class_basename($discount->model_type));

                return $discount;
            })
            ->toArray();

        return $discounts;
    }
}
