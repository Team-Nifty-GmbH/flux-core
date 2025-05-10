<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Seeder;

class ClientPaymentTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(bcfloor($clientIds->count() * 0.75));

        $paymentTypesIds = PaymentType::query()->get('id');
        $cutPaymentTypesIds = $paymentTypesIds->random(bcfloor($paymentTypesIds->count() * 0.75));

        foreach ($cutClientIds as $clientId) {
            $clientId->paymentTypes()->attach($cutPaymentTypesIds->random(
                rand(1, max(1, bcfloor($cutPaymentTypesIds->count() * 0.75)))
            ));
        }
    }
}
