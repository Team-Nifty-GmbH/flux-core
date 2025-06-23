<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\MailAccount;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class MailAccountUserTableSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.5));

        $mailAccountIds = MailAccount::query()->get('id');
        $cutMailAccountIds = $mailAccountIds->random(bcfloor($mailAccountIds->count() * 0.4));

        foreach ($cutUserIds as $user) {
            $mailAccountsToAttach = $cutMailAccountIds->random(rand(1, 3));

            foreach ($mailAccountsToAttach as $account) {
                $user->mailAccounts()->attach($account->id);
            }
        }
    }
}
