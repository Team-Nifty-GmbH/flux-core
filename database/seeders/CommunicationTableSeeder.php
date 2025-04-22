<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Product;
use Illuminate\Database\Seeder;

class CommunicationTableSeeder extends Seeder
{
    public function run(): void
    {
        $mailAccountIds = MailAccount::query()->get('id');
        $mailFolderIds = Product::query()->get('id');

        for ($i = 0; $i < 10; $i++) {
            Communication::factory()->create([
                'communication_type_enum' => CommunicationTypeEnum::Letter->value,
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            Communication::factory()->create([
                'communication_type_enum' => CommunicationTypeEnum::Mail->value,
                'mail_account_id' => $mailAccountIds->random()->getKey(),
                'mail_folder_id' => $mailFolderIds->random()->getKey(),
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            Communication::factory()->create([
                'communication_type_enum' => CommunicationTypeEnum::PhoneCall->value,
            ]);
        }
    }
}
