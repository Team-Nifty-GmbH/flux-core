<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Account;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use Illuminate\Database\Seeder;

class AccountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankConnections = BankConnection::all();
        $currencies = Currency::all();
        foreach ($bankConnections as $bankConnection) {
            Account::factory(3)->create([
                'bank_connection_id' => $bankConnection->id,
                'currency_id' => $currencies->random()->id,
            ]);
        }
    }
}
