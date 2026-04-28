<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\OrderType\UpdateOrderType;
use FluxErp\Livewire\DataTables\OrderTypeList;
use FluxErp\Livewire\Forms\OrderTypeForm;
use FluxErp\Models\Order;
use FluxErp\Models\Tenant;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\DataTable\AllowRecordMerging;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use FluxErp\Traits\Livewire\DataTable\SupportsLocalization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class OrderTypes extends OrderTypeList
{
    use Actions, AllowRecordMerging, DataTableHasFormEdit, SupportsLocalization;

    #[DataTableForm]
    public OrderTypeForm $orderType;

    public bool $isSelectable = true;

    protected ?string $includeBefore = 'flux::livewire.settings.order-types';

    public function sortRows(int|string $recordId, int $newPosition): void
    {
        try {
            UpdateOrderType::make([
                'id' => $recordId,
                'order_column' => max(1, $newPosition + 1),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return parent::getBuilder($builder)->ordered();
    }

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

    protected function isSortable(): bool
    {
        return true;
    }
}
