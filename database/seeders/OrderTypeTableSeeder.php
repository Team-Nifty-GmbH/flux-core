<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\OrderType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all(['id']);

        foreach ($clients as $client) {
            foreach (OrderTypeEnum::cases() as $orderType) {
                OrderType::factory()->create([
                    'name' => Str::headline($orderType->name),
                    'client_id' => $client->id,
                    'print_layouts' => match ($orderType) {
                        OrderTypeEnum::Order, OrderTypeEnum::SplitOrder, OrderTypeEnum::Subscription => [
                            'offer',
                            'invoice',
                            'order-confirmation',
                        ],
                        OrderTypeEnum::Retoure => ['retoure'],
                        default => [],
                    },
                    'order_type_enum' => $orderType,
                ]);
            }
        }
    }
}
