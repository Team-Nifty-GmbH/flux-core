<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $languages = Language::all();
        $currencies = Currency::all();
        $priceLists = PriceList::all();

        foreach ($clients as $client) {
            $contacts = Contact::query()
                ->with('addresses')
                ->where('client_id', $client->id)
                ->get();

            $orderTypes = OrderType::query()
                ->where('client_id', $client->id)
                ->get();

            $paymentTypes = PaymentType::query()
                ->where('client_id', $client->id)
                ->get();

            $orders = Order::query()
                ->where('client_id', $client->id)
                ->get();

            for ($i = 0; $i < 10; $i++) {
                $parentId = ! $orders ? $orders->random()->id : null;
                $paymentType = $paymentTypes->random();
                $orderType = $orderTypes->random();
                $currency = $currencies->random();

                $contact = $contacts->random();

                $orderModel = new Order();

                $order = Order::factory()->create([
                    'address_invoice_id' => $contact->addresses->random()->id,
                    'address_delivery_id' => $contact->addresses->random()->id,
                    'parent_id' => rand(0, 1) ? null : $parentId,
                    'client_id' => $client->id,
                    'currency_id' => $currency->id,
                    'language_id' => $languages->random()->id,
                    'order_type_id' => $orderType->id,
                    'price_list_id' => $priceLists->random()->id,
                    'delivery_type_id' => rand(0, 10),
                    'logistics_id' => rand(0, 10),
                    'payment_type_id' => $paymentType->id,
                    'tax_exemption_id' => rand(0, 10),
                    'delivery_state' => $orderModel->getStatesFor('delivery_state')->random(),
                    'payment_state' => $orderModel->getStatesFor('payment_state')->random(),
                ]);

                if ($order->is_locked) {
                    $order->setAttribute('invoice_date', faker()->date())
                        ->getSerialNumber('invoice_number', $order->client_id)
                        ->save();
                }
            }
        }
    }
}
