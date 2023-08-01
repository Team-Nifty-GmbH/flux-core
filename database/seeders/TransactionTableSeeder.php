<?php

namespace FluxErp\Database\Seeders;

use Illuminate\Database\Seeder;
use FluxErp\Models\Transaction;
use FluxErp\Models\Account;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;

class TransactionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::all();
        $currencies = Currency::all();
        $orders = Order::all();

        for ($i = 0; $i < 10; $i++) {
            Transaction::factory()->create([
                'account_id' => $accounts->random()->id,
                'currency_id' => $currencies->random()->id,
                'order_id' => $orders->random()->id,
            ]);
        }
    }
}
