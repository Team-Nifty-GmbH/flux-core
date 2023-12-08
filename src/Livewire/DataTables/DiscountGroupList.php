<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\DiscountGroup;
use TeamNiftyGmbH\DataTable\DataTable;

class DiscountGroupList extends DataTable
{
    protected string $model = DiscountGroup::class;

    public array $enabledCols = [
        'name',
        'is_active',
    ];

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
