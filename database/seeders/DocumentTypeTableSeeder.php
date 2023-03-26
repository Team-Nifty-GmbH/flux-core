<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = Client::all();

        for ($i = 0; $i < 10; $i++) {
            DocumentType::factory()->create([
                'client_id' => $clients->random()->id,
            ]);
        }
    }
}
