<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Models\Order;
use FluxErp\Support\Bus\BulkExecutor;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class OrderList extends BaseDataTable
{
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

    public bool $isSelectable = true;

    public bool $showModal = false;

    protected string $model = Order::class;

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('trash')
                ->text(__('Delete'))
                ->color('red')
                ->when(fn () => resolve_static(DeleteOrder::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete()',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Orders')]),
                ]),
        ];
    }

    public function delete(): void
    {
        $orders = resolve_static(Order::class, 'query')
            ->whereKey($this->getSelectedValues())
            ->where('is_locked', false)
            ->pluck('id');

        if ($orders->isEmpty()) {
            $this->reset('selected');

            return;
        }

        try {
            BulkExecutor::make(
                DeleteOrder::class,
                $orders
                    ->map(fn (int $orderId): array => ['id' => $orderId])
                    ->all()
            )
                ->name(__('Deleting orders'))
                ->dispatch();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->reset('selected');
    }

    public function getFormatters(): array
    {
        $formatters = parent::getFormatters();

        array_walk($formatters, function (&$formatter): void {
            if ($formatter === 'money') {
                $formatter = ['coloredMoney', ['property' => 'currency.iso']];
            }
        });

        return $formatters;
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['currency.iso']);
    }
}
