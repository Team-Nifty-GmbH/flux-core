<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\PaymentType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Seeder;

class PaymentTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all(['id']);

        $paymentTypes = PaymentType::factory()
            ->count(5)
            ->create();

        foreach ($tenants as $tenant) {
            $tenant->paymentTypes()->attach($paymentTypes->random(3));
        }
    }
}
