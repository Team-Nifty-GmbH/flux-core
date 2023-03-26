<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\DocumentType;
use FluxErp\Models\PaymentNotice;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentNoticeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = Client::all();
        $paymentType = PaymentType::all();
        $documentType = DocumentType::all();

        for ($i = 0; $i < 15; $i++) {
            PaymentNotice::factory()->create([
                'client_id' => $clients->random()->id,
                'payment_type_id' => $paymentType->random()->id,
                'document_type_id' => $documentType->random()->id,
            ]);
        }
    }
}
