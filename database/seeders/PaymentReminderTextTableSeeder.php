<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\PaymentReminderText;
use Illuminate\Database\Seeder;

class PaymentReminderTextTableSeeder extends Seeder
{
    public function run(): void
    {
        PaymentReminderText::factory()
            ->count(7)
            ->create();
    }
}
