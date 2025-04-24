<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\MailAccount;
use FluxErp\Models\Pivots\MailAccountUser;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class MailAccountUserTableSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.75));

        $mailAccountIds = MailAccount::query()->get('id');
        $cutMailAccountIds = $mailAccountIds->random(bcfloor($mailAccountIds->count() * 0.75));

        MailAccountUser::factory()->count(10)->create([
            'user_id' => fn () => $cutUserIds->random()->getKey(),
            'mail_account_id' => fn () => $cutMailAccountIds->random()->getKey(),
        ]);
    }
}
