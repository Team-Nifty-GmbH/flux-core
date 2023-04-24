<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\OrderType;
use Illuminate\Database\Seeder;

class OrderTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            foreach (OrderTypeEnum::values() as $orderType) {
                $printLayouts = $this->findPrintLayouts($orderType);
                OrderType::factory()->create([
                    'name' => $orderType,
                    'client_id' => $client->id,
                    'print_layouts' => $printLayouts,
                    'order_type_enum' => $orderType,
                ]);
            }
        }
    }

    protected function findPrintLayouts($orderType): array
    {
        $printLayouts = get_subclasses_of(
            extendingClass: 'FluxErp\View\Printing\Order\OrderView',
            namespace: 'FluxErp\View\Printing\Order'
        );
        $orderType = OrderTypeEnum::from($orderType);

        $filteredLayouts = match ($orderType) {
            OrderTypeEnum::Order, OrderTypeEnum::SplitOrder, OrderTypeEnum::Subscription => ['Offer'],
            OrderTypeEnum::Retoure => ['Retoure'],
            OrderTypeEnum::Purchase, OrderTypeEnum::PurchaseRefund => ['Invoice'],
            default => [],
        };

        return array_filter($printLayouts, function ($layout) use ($filteredLayouts) {
            return in_array(class_basename($layout), $filteredLayouts);
        });
    }
}
