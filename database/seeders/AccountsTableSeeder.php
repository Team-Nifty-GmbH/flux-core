<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Account;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use Illuminate\Database\Seeder;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bankConnections = BankConnection::all();
        $currencies = Currency::all();

        for ($i = 0; $i < 10; $i++) {
            Account::factory()->create([
                'bank_connection_id' => $bankConnections->random()->id,
                'currency_id' => $currencies->random()->id,
            ]);
        }
    }
}
