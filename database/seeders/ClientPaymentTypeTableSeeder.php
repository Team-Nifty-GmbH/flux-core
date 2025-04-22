<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\ClientPaymentType;
use Illuminate\Database\Seeder;

class ClientPaymentTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(bcfloor($clientIds->count() * 0.7));
        $paymentTypesIds = PaymentType::query()->get('id');
        $cutPaymentTypesIds = $paymentTypesIds->random(bcfloor($paymentTypesIds->count() * 0.7));

        for ($i = 0; $i < 10; $i++) {
            ClientPaymentType::factory()->create([
                'client_id' => $cutClientIds->random()->getKey(),
                'payment_type_id' => $cutPaymentTypesIds->random()->getKey(),
            ]);
        }
    }
}
