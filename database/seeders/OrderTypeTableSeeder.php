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
                OrderType::factory()->count(2)->create([
                    'client_id' => $client->id,
                    'order_type_enum' => $orderType,
                ]);
            }
        }
    }
}
