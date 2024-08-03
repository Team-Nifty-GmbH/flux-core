<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\DocumentGenerationSetting;
use FluxErp\Models\DocumentType;
use FluxErp\Models\OrderType;
use Illuminate\Database\Seeder;

class DocumentGenerationSettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $documentType = DocumentType::all();
        $orderType = OrderType::all();

        for ($i = 0; $i < 20; $i++) {
            DocumentGenerationSetting::factory()->create([
                'client_id' => $clients->random()->id,
                'document_type_id' => $documentType->random()->id,
                'order_type_id' => $orderType->random()->id,
            ]);
        }
    }
}
