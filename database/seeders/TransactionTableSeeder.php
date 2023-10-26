<?php

namespace FluxErp\Database\Seeders;

use App\Models\Order;
use FluxErp\Models\Account;
use FluxErp\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::all();
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
