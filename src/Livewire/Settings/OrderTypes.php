<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\OrderTypeList;
use FluxErp\Livewire\Forms\OrderTypeForm;
use FluxErp\Models\Order;
use FluxErp\Models\Tenant;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use FluxErp\Traits\Livewire\DataTable\SupportsLocalization;

class OrderTypes extends OrderTypeList
{
    use Actions, DataTableHasFormEdit, SupportsLocalization;

    #[DataTableForm]
    public OrderTypeForm $orderType;

    protected ?string $includeBefore = 'flux::livewire.settings.order-types';

    protected function getViewData(): array
    {
        $printViews = [];
        foreach (app(Order::class)->getAvailableViews() as $view) {
            $printViews[] = [
                'value' => $view,
                'label' => __($view),
            ];
        }

        return array_merge(
            parent::getViewData(),
            [
                'printViews' => $printViews,
                'tenants' => resolve_static(Tenant::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }
}
