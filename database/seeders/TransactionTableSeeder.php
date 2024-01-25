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
        $bankConnections = BankConnection::all(['id', 'currency_id']);
        $orders = Order::all(['id']);
        foreach ($bankConnections as $bankConnection) {
            Transaction::factory(20)->create([
                'bank_connection_id' => $bankConnection->id,
                'currency_id' => $bankConnection->currency_id,
                'order_id' => rand(0, 1) ? $orders->random()->id : null,
            ]);
        }
    }
}
