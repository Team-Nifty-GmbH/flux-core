<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Livewire\DataTables\StockPostingList as BaseStockPostingList;
use FluxErp\Livewire\Forms\StockPostingForm;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class StockPostingList extends BaseStockPostingList
{
    protected ?string $includeBefore = 'flux::livewire.product.stock-posting-list';

    #[Modelable]
    public ?int $warehouseId = null;

    public StockPostingForm $stockPosting;

    #[Locked]
    public int $productId;

    #[Locked]
    public bool $hasSerialNumbers = false;

    public function mount(): void
    {
        $this->hasSerialNumbers = resolve_static(Product::class, 'query')
            ->whereKey($this->productId)
            ->value('has_serial_numbers');

        parent::mount();
    }

    public function updatedWarehouseId(): void
    {
        $this->userFilters = [[
            [
                'column' => 'warehouse_id',
                'operator' => '=',
                'value' => $this->warehouseId,
            ],
        ]];
    }

    public function create(): void
    {
        $this->stockPosting->reset();
        $this->stockPosting->warehouse_id =
            $this->warehouseId ?? resolve_static(Warehouse::class, 'default')->id;

        $this->js(<<<'JS'
            $modalOpen('create-stock-posting');
        JS);
    }

    public function save(): bool
    {
        $this->stockPosting->product_id = $this->productId;

        if (! $this->hasSerialNumbers) {
            $this->stockPosting->serial_number = [];
        }

        try {
            $this->stockPosting->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();
        $this->dispatch('loadData')->to('product.warehouse-list');

        return true;
    }

    #[Renderless]
    public function viewOrder(int $orderPositionId): void
    {
        $orderId = resolve_static(OrderPosition::class, 'query')
            ->whereKey($orderPositionId)
            ->value('order_id');

        if ($orderId) {
            $this->redirect(route('orders.id', $orderId));
        }
    }

    protected function getViewData(): array
    {
        $viewData = [
            'warehouses' => resolve_static(Warehouse::class, 'query')
                ->pluck('name', 'id')
                ->toArray(),
        ];

        if ($this->hasSerialNumbers) {
            $viewData['serialNumberRanges'] = resolve_static(SerialNumberRange::class, 'query')
                ->where('model_type', morph_alias(Product::class))
                ->where('model_id', $this->productId)
                ->pluck('type', 'id')
                ->toArray();
        }

        return array_merge(
            parent::getViewData(),
            $viewData
        );
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New Stock Posting'))
                ->icon('plus')
                ->color('indigo')
                ->wireClick('create')
                ->when(resolve_static(CreateStockPosting::class, 'canPerformAction', [false])),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('View Order'))
                ->color('indigo')
                ->icon('eye')
                ->attributes([
                    'x-show' => 'record.order_position_id',
                    'x-on:click' => '$wire.viewOrder(record.order_position_id)',
                ]),
        ];
    }

    protected function getComponentAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-init' => <<<'JS'
                $watch('$wire.warehouseId', () => {
                    $wire.loadData();
                })
            JS,
        ]);
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->where('product_id', $this->productId);
    }

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'order_position_id',
            ]
        );
    }
}
