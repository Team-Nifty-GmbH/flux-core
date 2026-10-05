<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\OrderPositionStockPosting;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use FluxErp\Rulesets\StockPosting\CreateStockPostingRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateStockPosting extends FluxAction
{
    public static function models(): array
    {
        return [StockPosting::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateStockPostingRuleset::class;
    }

    public function performAction(): StockPosting
    {
        $serialNumberData = Arr::pull($this->data, 'serial_number');
        $address = Arr::pull($this->data, 'address');

        if ($serialNumberData) {
            $serialNumber = resolve_static(
                CreateSerialNumber::class,
                'make',
                ['data' => $serialNumberData]
            )
                ->checkPermission()
                ->validate()
                ->execute();

            $this->data['serial_number_id'] = $serialNumber->id;
        }

        $stockPosting = app(StockPosting::class, ['attributes' => $this->getData()]);
        $stockPosting->save();

        $serialNumber ??= $stockPosting->serialNumber;
        if ($serialNumber && $stockPosting->order_position_id && is_null($address)) {
            $address = [
                'id' => resolve_static(Order::class, 'query')
                    ->whereHas('orderPositions', fn ($query) => $query->where('id', $stockPosting->order_position_id))
                    ->value('address_delivery_id'),
                'quantity' => $stockPosting->posting,
            ];
        }

        if ($address && $serialNumber) {
            $serialNumber->addresses()->attach($address['id'], ['quantity' => data_get($address, 'quantity', 1)]);
        }

        return $stockPosting->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $posting = (string) $this->getData('posting');
        $errors = [];

        if (bccomp($posting, '0', 10) === 1) {
            $requiresStorageArea = resolve_static(Warehouse::class, 'query')
                ->whereKey($this->getData('warehouse_id'))
                ->value('requires_storage_area');

            if ($requiresStorageArea && ! $this->getData('storage_area_id')) {
                $errors['storage_area_id'][] = 'The given warehouse requires a storage area';
            }

            if ($storageAreaId = $this->getData('storage_area_id')) {
                $storageArea = resolve_static(StorageArea::class, 'query')
                    ->whereKey($storageAreaId)
                    ->first();

                if (! $storageArea?->is_storage_location || ! $storageArea->is_active) {
                    $errors['storage_area_id'][] = 'The given storage area cannot hold stock';
                }
            }

            $isLotTracked = resolve_static(Product::class, 'query')
                ->whereKey($this->getData('product_id'))
                ->value('is_lot_tracked');

            if ($isLotTracked && ! $this->getData('lot_id')) {
                $errors['lot_id'][] = 'The given product requires a lot';
            }
        }

        if ($this->getData('parent_id') && bccomp($posting, '0', 10) === -1) {
            $parent = resolve_static(StockPosting::class, 'query')
                ->whereKey($this->getData('parent_id'))
                ->first();

            $reserved = $this->getData('order_position_id')
                ? resolve_static(OrderPositionStockPosting::class, 'query')
                    ->where('order_position_id', $this->getData('order_position_id'))
                    ->where('stock_posting_id', $this->getData('parent_id'))
                    ->sum('reserved_amount')
                : 0;

            $drawable = bcadd(
                (string) $parent->remaining_stock,
                (string) $reserved,
                10
            );

            if (bccomp(bcabs($posting), $drawable, 10) === 1) {
                $errors['posting'][] = 'The withdrawal exceeds the drawable stock of the parent posting';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createStockPosting');
        }
    }

    protected function prepareForValidation(): void
    {
        if ($this->getData('serial_number.use_supplier_serial_number')) {
            data_set(
                $this->data,
                'serial_number.serial_number',
                $this->getData('serial_number.supplier_serial_number')
            );
        }
    }
}
