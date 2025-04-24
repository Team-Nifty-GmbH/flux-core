<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Database\Seeder;

class CommunicationTableSeeder extends Seeder
{
    public function run(): void
    {
        $mailAccountIds = MailAccount::query()->get('id');
        $cutMailAccountIds = $mailAccountIds->random(bcfloor($mailAccountIds->count() * 0.75));

        $mailFolderIds = MailFolder::query()->get('id');
        $cutMailFolderIds = $mailFolderIds->random(bcfloor($mailFolderIds->count() * 0.75));

        Communication::factory()->count(10)->create([
            'communication_type_enum' => CommunicationTypeEnum::Letter->value,
        ]);

        Communication::factory()->count(10)->create([
            'communication_type_enum' => CommunicationTypeEnum::Mail->value,
            'mail_account_id' => fn () => $cutMailAccountIds->random()->getKey(),
            'mail_folder_id' => fn () => $cutMailFolderIds->random()->getKey(),
        ]);

        Communication::factory()->count(10)->create([
            'communication_type_enum' => CommunicationTypeEnum::PhoneCall->value,
        ]);
    }
}
