<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Cart;
use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Database\Seeder;

class ClientPaymentTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $paymentTypesIds = PaymentType::query()->get('id');

        foreach ($clientIds as $clientId) {
            $clientId->paymentTypes()->attach($paymentTypesIds->random(rand(1, 5)));
        }
    }
}
