<?php

namespace FluxErp\Database\Seeders;

use Illuminate\Database\Seeder;
use FluxErp\Models\Transaction;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Transaction::factory()->count(10)->create();
    }
}
