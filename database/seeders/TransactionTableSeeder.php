<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionTableSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = BankConnection::all();
        $orders = Order::all();
        foreach ($accounts as $account) {
            Transaction::factory(20)->create([
                'account_id' => $accounts->id,
                'currency_id' => $account->currency_id,
                'order_id' => rand(0, 1) ? $orders->random()->id : null,
            ]);
        }
    }
}
