<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\MailAccount;
use Illuminate\Database\Seeder;

class MailAccountTableSeeder extends Seeder
{
    public function run(): void
    {
        MailAccount::factory()->count(5)->create();
    }
}
