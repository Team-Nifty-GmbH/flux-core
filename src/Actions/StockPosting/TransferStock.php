<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
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
        $description = $this->getData('description', 'Stock transfer');

        $allocation = $this->allocator()->allocate($this->getData('amount'));
        $allocated = $allocation->reduce(
            fn (string $carry, array $item) => bcadd($carry, $item['amount'], 10),
            '0'
        );

        if (bccomp($allocated, (string) $this->getData('amount'), 10) === -1) {
            throw ValidationException::withMessages([
                'amount' => ['The source storage area does not hold enough stock'],
            ])
                ->errorBag('transferStock');
        }

        foreach ($allocation as $item) {
            $stockPosting = $item['stockPosting'];
            $amount = $item['amount'];

            CreateStockPosting::make([
                'warehouse_id' => $this->getData('warehouse_id'),
                'product_id' => $this->getData('product_id'),
                'storage_area_id' => $this->getData('from_storage_area_id'),
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
                'warehouse_id' => $this->getData('warehouse_id'),
                'product_id' => $this->getData('product_id'),
                'storage_area_id' => $this->getData('to_storage_area_id'),
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

        $storageAreas = resolve_static(StorageArea::class, 'query')
            ->whereKey([$this->getData('from_storage_area_id'), $this->getData('to_storage_area_id')])
            ->get()
            ->keyBy('id');

        $source = $storageAreas->get($this->getData('from_storage_area_id'));
        $target = $storageAreas->get($this->getData('to_storage_area_id'));
        $warehouseId = (int) $this->getData('warehouse_id');
        $errors = [];

        if ($source && $source->warehouse_id !== $warehouseId) {
            $errors['from_storage_area_id'][] = 'The source storage area belongs to a different warehouse';
        }

        if ($target && $target->warehouse_id !== $warehouseId) {
            $errors['to_storage_area_id'][] = 'The target storage area belongs to a different warehouse';
        }

        if ($target && (! $target->is_storage_location || ! $target->is_active)) {
            $errors['to_storage_area_id'][] = 'The target storage area cannot hold stock';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('transferStock');
        }
    }

    protected function allocator(): StockAllocator
    {
        return app(StockAllocator::class)
            ->forProduct($this->getData('product_id'))
            ->inWarehouse($this->getData('warehouse_id'))
            ->inStorageAreas([$this->getData('from_storage_area_id')])
            ->forLot($this->getData('lot_id', null));
    }
}
