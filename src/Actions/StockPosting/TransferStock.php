<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use FluxErp\Rulesets\StockPosting\TransferStockRuleset;
use FluxErp\Support\Stock\StockAllocator;
use Illuminate\Support\Collection;
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

    public function performAction(): Collection
    {
        $description = $this->getData('description', 'Stock transfer');
        $fromStorageAreaId = $this->getData('from_storage_area_id');
        $serialNumberIds = $this->getData('serial_number_ids') ?: null;

        $allocation = StockAllocator::make(
            productId: $this->getData('product_id'),
            warehouseId: $this->getData('warehouse_id'),
            storageAreaIds: $fromStorageAreaId ? [$fromStorageAreaId] : null,
            lotId: $this->getData('lot_id'),
            serialNumberIds: $serialNumberIds,
            withoutStorageArea: ! $fromStorageAreaId,
            stockPostingIds: $this->getData('stock_posting_ids') ?: null,
        )
            ->allocate($this->getData('amount'));

        $allocated = $allocation->reduce(
            fn (string $carry, array $item) => bcadd($carry, $item['amount'], 10),
            '0'
        );

        if (
            bccomp($allocated, (string) $this->getData('amount'), 10) === -1
            || (
                $serialNumberIds
                && $allocation->pluck('stockPosting.serial_number_id')
                    ->unique()
                    ->count() !== count($serialNumberIds)
            )
        ) {
            throw ValidationException::withMessages([
                'amount' => ['The source storage area does not hold enough stock'],
            ])
                ->errorBag('transferStock');
        }

        $incoming = collect();
        foreach ($allocation as $item) {
            $stockPosting = $item['stockPosting'];
            $amount = $item['amount'];

            CreateStockPosting::make([
                'lot_id' => $stockPosting->lot_id,
                'parent_id' => $stockPosting->id,
                'product_id' => $this->getData('product_id'),
                'serial_number_id' => $stockPosting->serial_number_id,
                'storage_area_id' => $fromStorageAreaId,
                'warehouse_id' => $this->getData('warehouse_id'),
                'posting' => bcmul($amount, -1),
                'purchase_price' => $stockPosting->purchase_price,
                'description' => $description,
            ])
                ->validate()
                ->execute();

            UpdateStockPosting::make([
                'id' => $stockPosting->id,
                'remaining_stock' => bcsub((string) $stockPosting->remaining_stock, $amount, 10),
            ])
                ->validate()
                ->execute();

            $incoming->push(
                CreateStockPosting::make([
                    'lot_id' => $stockPosting->lot_id,
                    'parent_id' => $stockPosting->id,
                    'product_id' => $this->getData('product_id'),
                    'serial_number_id' => $stockPosting->serial_number_id,
                    'storage_area_id' => $this->getData('to_storage_area_id'),
                    'warehouse_id' => $this->getData('to_warehouse_id'),
                    'posting' => $amount,
                    'purchase_price' => $stockPosting->purchase_price,
                    'description' => $description,
                ])
                    ->validate()
                    ->execute()
            );
        }

        return $incoming;
    }

    protected function prepareForValidation(): void
    {
        $this->data['to_warehouse_id'] ??= $this->getData('warehouse_id');
    }

    protected function validateData(): void
    {
        parent::validateData();

        $fromStorageAreaId = $this->getData('from_storage_area_id');
        $toStorageAreaId = $this->getData('to_storage_area_id');
        $isSameWarehouse = (int) $this->getData('warehouse_id') === (int) $this->getData('to_warehouse_id');

        $warehouses = resolve_static(Warehouse::class, 'query')
            ->whereKey([$this->getData('warehouse_id'), $this->getData('to_warehouse_id')])
            ->pluck('requires_storage_area', 'id');
        $storageAreas = resolve_static(StorageArea::class, 'query')
            ->whereKey(array_filter([$fromStorageAreaId, $toStorageAreaId]))
            ->get()
            ->keyBy('id');

        $source = $storageAreas->get($fromStorageAreaId);
        $target = $storageAreas->get($toStorageAreaId);
        $errors = [];

        if (! $fromStorageAreaId) {
            if ($warehouses->get($this->getData('warehouse_id'))) {
                $errors['from_storage_area_id'][] = 'The source warehouse requires a storage area';
            }
        } elseif ($source?->warehouse_id !== (int) $this->getData('warehouse_id')) {
            $errors['from_storage_area_id'][] = 'The source storage area belongs to a different warehouse';
        }

        if (! $toStorageAreaId) {
            if ($warehouses->get($this->getData('to_warehouse_id'))) {
                $errors['to_storage_area_id'][] = 'The target warehouse requires a storage area';
            } elseif ($isSameWarehouse && ! $fromStorageAreaId) {
                $errors['to_storage_area_id'][] = 'The target equals the source';
            }
        } elseif ($target?->warehouse_id !== (int) $this->getData('to_warehouse_id')) {
            $errors['to_storage_area_id'][] = 'The target storage area belongs to a different warehouse';
        } elseif (! $target->is_storage_location || ! $target->is_active) {
            $errors['to_storage_area_id'][] = 'The target storage area cannot hold stock';
        }

        $serialNumberIds = $this->getData('serial_number_ids');
        if (
            $serialNumberIds
            && bccomp((string) count($serialNumberIds), (string) $this->getData('amount'), 10) !== 0
        ) {
            $errors['serial_number_ids'][] = 'The number of serial numbers must match the amount';
        }

        $stockPostingIds = $this->getData('stock_posting_ids');
        if (
            $stockPostingIds
            && resolve_static(StockPosting::class, 'query')
                ->whereKey($stockPostingIds)
                ->where('product_id', $this->getData('product_id'))
                ->where('warehouse_id', $this->getData('warehouse_id'))
                ->where('storage_area_id', $fromStorageAreaId)
                ->count() !== count($stockPostingIds)
        ) {
            $errors['stock_posting_ids'][] = 'The stock postings do not belong to the source of the transfer';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('transferStock');
        }
    }
}
