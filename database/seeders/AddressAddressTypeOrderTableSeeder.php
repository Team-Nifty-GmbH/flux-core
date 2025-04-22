<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AddressAddressTypeOrderTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::withoutEvents(function (): void {
            $orderIds = Order::pluck('id')->toArray();
            $addressIds = Address::pluck('id')->toArray();
            $addressTypeIds = AddressType::pluck('id')->toArray();

            foreach ($orderIds as $orderId) {
                $pickCount = rand(1, 3);
                $combos = [];

                while (count($combos) < $pickCount) {
                    $addrId = Arr::random($addressIds);
                    $typeId = Arr::random($addressTypeIds);
                    $key = "{$orderId}-{$addrId}-{$typeId}";

                    if (! isset($combos[$key])) { // checks for duplicates
                        $data = AddressAddressTypeOrder::factory()->make([
                            'order_id' => $orderId,
                            'address_id' => $addrId,
                            'address_type_id' => $typeId,
                        ])->toArray();

                        if (isset($data['address']) && is_array($data['address'])) {
                            $data['address'] = json_encode($data['address']);
                        }

                        $combos[$key] = $data;
                    }
                }

                AddressAddressTypeOrder::insertOrIgnore(array_values($combos));
            }
        });
    }
}
