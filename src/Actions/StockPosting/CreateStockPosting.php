<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Models\Order;
use FluxErp\Models\StockPosting;
use FluxErp\Rulesets\StockPosting\CreateStockPostingRuleset;
use Illuminate\Support\Arr;

class CreateStockPosting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateStockPostingRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [StockPosting::class];
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

        $stockPosting = app(StockPosting::class, ['attributes' => $this->data]);
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

    protected function prepareForValidation(): void
    {
        if (data_get($this->data, 'serial_number.use_supplier_serial_number')) {
            data_set(
                $this->data, 'serial_number.serial_number',
                data_get($this->data, 'serial_number.supplier_serial_number')
            );
        }
    }
}
