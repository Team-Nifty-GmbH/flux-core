<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Database\Seeder;

class PurchaseInvoiceTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(bcfloor($clientIds->count() * 0.7));

        $contactIds = Contact::query()->get('id');
        $cutContactIds = $contactIds->random(bcfloor($contactIds->count() * 0.7));

        $currencyIds = Currency::query()->get('id');
        $cutCurrencyIds = $currencyIds->random(bcfloor($currencyIds->count() * 0.7));

        $mediaIds = Media::query()->get('id');
        $cutMediaIds = $mediaIds->random(bcfloor($mediaIds->count() * 0.7));

        $orderIds = Order::query()->get('id');
        $cutOrderIds = $orderIds->random(bcfloor($orderIds->count() * 0.7));

        $orderTypeIds = OrderType::query()->get('id');
        $cutOrderTypeIds = $orderTypeIds->random(bcfloor($orderTypeIds->count() * 0.7));

        $paymentTypeIds = PaymentType::query()->get('id');
        $cutPaymentTypeIds = $paymentTypeIds->random(bcfloor($paymentTypeIds->count() * 0.7));

        PurchaseInvoice::factory()->count(10)->create([
            'client_id' => fn () => faker()->boolean(75) ? $cutClientIds->random()->getKey() : null,
            'contact_id' => fn () => faker()->boolean(50) ? $cutContactIds->random()->getKey() : null,
            'currency_id' => fn () => faker()->boolean(75) ? $cutCurrencyIds->random()->getKey() : null,
            'media_id' => fn () => faker()->boolean(50) ? $cutMediaIds->random()->getKey() : null,
            'order_id' => fn () => faker()->boolean(75) ? $cutOrderIds->random()->getKey() : null,
            'order_type_id' => fn () => faker()->boolean(50) ? $cutOrderTypeIds->random()->getKey() : null,
            'payment_type_id' => fn () => faker()->boolean(75) ? $cutPaymentTypeIds->random()->getKey() : null,
        ]);
    }
}
