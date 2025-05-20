<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Database\Seeder;

class MailFolderTableSeeder extends Seeder
{
    public function run(): void
    {
        $layers = rand(1, 2); // specify how many folder layers should be created

        $mailAccountIds = MailAccount::query()->pluck('id')->random(6);

        $mailFolders = MailFolder::factory() // first layer
            ->count(10)
            ->make()
            ->each(function ($folder) use ($mailAccountIds): void {
                $folder->mail_account_id = $mailAccountIds->random();
                $folder->save();
            });

        for ($i = 0; $i <= $layers; $i++) {
            $newMailFolders = MailFolder::factory()
                ->count(10)
                ->make()
                ->each(function ($folder) use ($mailAccountIds, $mailFolders): void {
                    $folder->mail_account_id = $mailAccountIds->random();
                    $folder->parent_id = $mailFolders->random()->getKey();
                    $folder->save();
                });

            $mailFolders = $newMailFolders;
        }
    }
}
