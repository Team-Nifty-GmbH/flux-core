<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\BankConnection;
use FluxErp\Models\PaymentRun;
use Illuminate\Database\Seeder;

class PaymentRunTableSeeder extends Seeder
{
    public function run(): void
    {
        $bankConnectionIds = BankConnection::query()->get('id');
        $cutBankConnectionIds = $bankConnectionIds->random(max(1, bcfloor($bankConnectionIds->count() * 0.7)));

        PaymentRun::factory()->count(15)->create([
            'bank_connection_id' => fn () => $cutBankConnectionIds->random()->getKey(),
        ]);
    }
}
