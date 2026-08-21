<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StockPosting;
use FluxErp\Models\WarehouseBin;
use FluxErp\Rulesets\StockPosting\TransferStockRuleset;
use FluxErp\Support\Stock\StockAllocator;
use Illuminate\Validation\ValidationException;

class TransferStock extends FluxAction
{
    public static function models(): array
    {
        return [StockPosting::class];
    }

    protected function getRulesets(): string|array
    {
        return TransferStockRuleset::class;
    }

    public function performAction(): bool
    {
        $description = $this->data['description'] ?? __('Stock transfer');

        $allocation = $this->allocator()->allocate($this->data['amount']);
        $allocated = $allocation->reduce(
            fn (string $carry, array $item) => bcadd($carry, $item['amount'], 10),
            '0'
        );

        if (bccomp($allocated, (string) $this->data['amount'], 10) === -1) {
            throw ValidationException::withMessages([
                'amount' => ['The source bin does not hold enough stock'],
            ])->errorBag('transferStock');
        }

        foreach ($allocation as $item) {
            $stockPosting = $item['stockPosting'];
            $amount = $item['amount'];

            CreateStockPosting::make([
                'warehouse_id' => $this->data['warehouse_id'],
                'product_id' => $this->data['product_id'],
                'warehouse_bin_id' => $this->data['from_warehouse_bin_id'],
                'lot_id' => $stockPosting->lot_id,
                'parent_id' => $stockPosting->id,
                'serial_number_id' => $stockPosting->serial_number_id,
                'posting' => bcmul($amount, -1),
                'purchase_price' => $stockPosting->purchase_price,
                'description' => $description,
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            UpdateStockPosting::make([
                'id' => $stockPosting->id,
                'remaining_stock' => bcsub((string) $stockPosting->remaining_stock, $amount, 10),
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            CreateStockPosting::make([
                'warehouse_id' => $this->data['warehouse_id'],
                'product_id' => $this->data['product_id'],
                'warehouse_bin_id' => $this->data['to_warehouse_bin_id'],
                'lot_id' => $stockPosting->lot_id,
                'parent_id' => $stockPosting->id,
                'serial_number_id' => $stockPosting->serial_number_id,
                'posting' => $amount,
                'purchase_price' => $stockPosting->purchase_price,
                'description' => $description,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        }

        return true;
    }

    protected function validateData(): void
    {
        parent::validateData();

        $bins = resolve_static(WarehouseBin::class, 'query')
            ->whereKey([$this->data['from_warehouse_bin_id'], $this->data['to_warehouse_bin_id']])
            ->get()
            ->keyBy('id');

        $target = $bins->get($this->data['to_warehouse_bin_id']);
        $source = $bins->get($this->data['from_warehouse_bin_id']);

        if ($source?->warehouse_id !== (int) $this->data['warehouse_id']) {
            throw ValidationException::withMessages([
                'from_warehouse_bin_id' => ['The source bin belongs to a different warehouse'],
            ])->errorBag('transferStock');
        }

        if ($target?->warehouse_id !== (int) $this->data['warehouse_id']) {
            throw ValidationException::withMessages([
                'to_warehouse_bin_id' => ['The target bin belongs to a different warehouse'],
            ])->errorBag('transferStock');
        }

        if (! $target->is_storage_location || ! $target->is_active) {
            throw ValidationException::withMessages([
                'to_warehouse_bin_id' => ['The target bin cannot hold stock'],
            ])->errorBag('transferStock');
        }

        $available = (string) $this->allocator()->query()->sum('remaining_stock');

        if (bccomp($available, (string) $this->data['amount'], 10) === -1) {
            throw ValidationException::withMessages([
                'amount' => ['The source bin does not hold enough stock'],
            ])->errorBag('transferStock');
        }
    }

    protected function allocator(): StockAllocator
    {
        return app(StockAllocator::class)
            ->forProduct($this->data['product_id'])
            ->inWarehouse($this->data['warehouse_id'])
            ->inBins([$this->data['from_warehouse_bin_id']])
            ->forLot($this->data['lot_id'] ?? null);
    }
}
