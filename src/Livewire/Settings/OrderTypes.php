<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\OrderType\CreateOrderType;
use FluxErp\Actions\OrderType\UpdateOrderType;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\DataTables\OrderTypeList;
use FluxErp\Livewire\Forms\OrderTypeForm;
use FluxErp\Models\Client;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderTypes extends OrderTypeList
{
    use Actions;

    protected ?string $includeBefore = 'flux::livewire.settings.order-types';

    public OrderTypeForm $orderType;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(resolve_static(CreateOrderType::class, 'canPerformAction', [false]))
                ->attributes(
                    ['wire:click' => 'edit']
                ),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->icon('pencil')
                ->attributes([
                    'x-on:click' => '$wire.edit(record.id)',
                ])
                ->when(fn () => resolve_static(UpdateOrderType::class, 'canPerformAction', [false])),
        ];
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
                'clients' => resolve_static(Client::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'enum' => OrderTypeEnum::values(),
            ]
        );
    }

    public function save(): bool
    {
        try {
            $this->orderType->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function edit(OrderType $orderType): void
    {
        $this->orderType->reset();
        $this->orderType->fill($orderType);

        $this->js(<<<'JS'
            $openModal('edit-order-type');
        JS);
    }

    public function delete(): bool
    {
        try {
            $this->orderType->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
