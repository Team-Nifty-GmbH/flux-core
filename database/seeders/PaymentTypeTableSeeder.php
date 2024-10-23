<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all(['id']);

        $paymentTypes = PaymentType::factory()
            ->count(5)
            ->create();

        foreach ($clients as $client) {
            $client->paymentTypes()->attach($paymentTypes->random(3));
        }
    }
}
