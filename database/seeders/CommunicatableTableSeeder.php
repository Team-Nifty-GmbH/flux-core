<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\Communicatable;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\SepaMandate;
use FluxErp\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CommunicatableTableSeeder extends Seeder
{
    public function run(): void
    {
        $communicationIds = Communication::query()->get('id');
        $CutCommunicationIds = $communicationIds->random(bcfloor($communicationIds->count() * 0.7));

        for ($i = 0; $i < 100; $i++) {
            $modelClass = Arr::random([
                Contact::class,
                Order::class,
                SepaMandate::class,
                PurchaseInvoice::class,
                Ticket::class,
                Address::class,
            ]);

            $idList = $modelClass::query()->pluck('id')->toArray();
            $instanceId = faker()->unique()->randomElement($idList);

            Communicatable::firstOrCreate([
                'communicatable_type' => $modelClass,
                'communicatable_id' => $instanceId,
                'communication_id' => $CutCommunicationIds->random()->getKey(),
            ]);
        }
    }
}
