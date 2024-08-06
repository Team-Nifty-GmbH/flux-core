<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\DocumentGenerationSetting;
use FluxErp\Models\DocumentType;
use FluxErp\Models\OrderType;
use Illuminate\Database\Seeder;

class DocumentGenerationSettingTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all(['id']);
        $documentType = DocumentType::all(['id']);
        $orderType = OrderType::all(['id']);

        for ($i = 0; $i < 20; $i++) {
            DocumentGenerationSetting::factory()->create([
                'client_id' => $clients->random()->id,
                'document_type_id' => $documentType->random()->id,
                'order_type_id' => $orderType->random()->id,
            ]);
        }
    }
}
