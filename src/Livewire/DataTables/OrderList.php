<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Models\Order;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class OrderList extends BaseDataTable
{
    use HasEloquentListeners;

    protected string $model = Order::class;

    public bool $isSelectable = true;

    public array $enabledCols = [
        'order_type.name',
        'order_date',
        'order_number',
        'invoice_number',
        'contact.customer_number',
        'address_invoice.name',
        'total_net_price',
        'balance',
        'payment_state',
        'commission',
    ];

    public bool $showModal = false;

    public function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('trash')
                ->label(__('Delete'))
                ->color('negative')
                ->when(fn () => resolve_static(DeleteOrder::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Orders')]),
                ]),
        ];
    }

    public function getFormatters(): array
    {
        $formatters = parent::getFormatters();

        array_walk($formatters, function (&$formatter) {
            if ($formatter === 'money') {
                $formatter = ['coloredMoney', ['property' => 'currency.iso']];
            }
        });

        return $formatters;
    }

    public function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['currency.iso']);
    }

    public function delete(): void
    {
        $orders = app(Order::class)->query()
            ->whereIntegerInRaw('id', $this->selected)
            ->where('is_locked', false)
            ->pluck('id');

        $deleted = 0;
        foreach ($orders as $orderId) {
            try {
                $success = DeleteOrder::make(['id' => $orderId])->checkPermission()->validate()->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                continue;
            }

            if ($success) {
                $deleted++;
            }
        }

        $this->notification()->success(__('Deleted :count orders', ['count' => $deleted]));

        if ($deleted > 0) {
            $this->loadData();
        }

        $this->selected = [];
    }
}
