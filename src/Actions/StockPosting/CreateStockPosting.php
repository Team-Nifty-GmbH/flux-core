<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Models\Order;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
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

        if (bccomp($posting, '0', 10) === 1) {
            $requiresBinLocation = resolve_static(Warehouse::class, 'query')
                ->whereKey($this->getData('warehouse_id'))
                ->value('requires_bin_location');

            if ($requiresBinLocation && ! ($this->getData('warehouse_bin_id', false))) {
                throw ValidationException::withMessages([
                    'warehouse_bin_id' => ['The given warehouse requires a bin location'],
                ])->errorBag('createStockPosting');
            }

            if ($warehouseBinId = $this->getData('warehouse_bin_id', false)) {
                $warehouseBin = resolve_static(WarehouseBin::class, 'query')
                    ->whereKey($warehouseBinId)
                    ->first();

                if (! $warehouseBin?->is_storage_location || ! $warehouseBin->is_active) {
                    throw ValidationException::withMessages([
                        'warehouse_bin_id' => ['The given warehouse bin cannot hold stock'],
                    ])->errorBag('createStockPosting');
                }
            }

            $isLotTracked = resolve_static(Product::class, 'query')
                ->whereKey($this->getData('product_id'))
                ->value('is_lot_tracked');

            if ($isLotTracked && ! ($this->getData('lot_id', false))) {
                throw ValidationException::withMessages([
                    'lot_id' => ['The given product requires a lot'],
                ])->errorBag('createStockPosting');
            }
        }

        if (($this->getData('parent_id', false)) && bccomp($posting, '0', 10) === -1) {
            $parent = resolve_static(StockPosting::class, 'query')
                ->whereKey($this->getData('parent_id'))
                ->first();

            $drawable = bcadd(
                (string) $parent->remaining_stock,
                (string) $parent->reserved_stock,
                10
            );

            if (bccomp(bcabs($posting), $drawable, 10) === 1) {
                throw ValidationException::withMessages([
                    'posting' => ['The withdrawal exceeds the drawable stock of the parent posting'],
                ])->errorBag('createStockPosting');
            }
        }
    }

    protected function prepareForValidation(): void
    {
        if (data_get($this->getData(), 'serial_number.use_supplier_serial_number')) {
            data_set(
                $this->getData(), 'serial_number.serial_number',
                data_get($this->getData(), 'serial_number.supplier_serial_number')
            );
        }
    }
}
