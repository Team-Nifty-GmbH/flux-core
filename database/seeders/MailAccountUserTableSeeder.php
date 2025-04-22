<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Cart;
use FluxErp\Models\MailAccount;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\MailAccountUser;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class MailAccountUserTableSeeder extends Seeder
{
    public function run(): void
    {
        $userIds= User::query()->get('id');
        $mailAccountIds = MailAccount::query()->get('id');

        for ($i = 0; $i < 10; $i++) {
            MailAccountUser::factory()->create([
                'user_id' => $userIds->random()->getKey(),
                'mail_account_id' => $mailAccountIds->random()->getKey(),
            ]);
        }
    }
}
