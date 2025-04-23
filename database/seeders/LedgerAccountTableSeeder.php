<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Database\Seeder;

class LedgerAccountTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(bcfloor($clientIds->count() * 0.75));

        LedgerAccount::factory()->count(9)->create([
            'client_id' => fn() => $cutClientIds->random()->getKey(),
        ]);
    }
}
