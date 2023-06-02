<?php

namespace FluxErp\Database\Seeders;

use Illuminate\Database\Seeder;
use FluxErp\Models\Account;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Account::factory()->count(10)->create();
    }
}
