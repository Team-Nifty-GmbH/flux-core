<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all(['id']);
        $languages = Language::all(['id']);
        $currencies = Currency::all(['id']);
        $priceLists = PriceList::all(['id']);
        $users = User::all(['id']);
        $bankConnections = BankConnection::all(['id']);

        foreach ($clients as $client) {
            $contacts = Contact::query()
                ->with('addresses')
                ->where('has_delivery_lock', false)
                ->where('client_id', $client->id)
                ->get(['id']);

            $orderTypes = OrderType::query()
                ->where('client_id', $client->id)
                ->get(['id']);

            $paymentTypes = PaymentType::query()
                ->whereRelation('clients', 'id', $client->id)
                ->get(['id']);

            $orders = Order::query()
                ->where('client_id', $client->id)
                ->get(['id']);
            $orderModel = new Order();

            for ($i = 0; $i < 10; $i++) {
                $parentId = ! $orders ? $orders->random()->id : null;
                $paymentType = $paymentTypes->random();
                $orderType = $orderTypes->random();
                $currency = $currencies->random();
                $contact = $contacts->random();

                $order = Order::factory()
                    ->create([
                        'address_invoice_id' => $contact->addresses->random()->id,
                        'address_delivery_id' => $contact->addresses->random()->id,
                        'agent_id' => faker()->boolean(40) ? $users->random()->id : null,
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
                    $transaction = Transaction::factory()->create([
                        'bank_connection_id' => $bankConnections->random()->id,
                        'currency_id' => $currency->id,
                    ]);

                    OrderTransaction::factory()->create([
                        'transaction_id' => $transaction->id,
                        'order_id' => $order->id,
                        'amount' => faker()->boolean(80)
                            ? $order->total_gross_price ?? 0
                            : ($order->total_gross_price ?? 0) - rand(1, $order->total_gross_price ?? 0),
                    ]);

                    $order->setAttribute(
                        'invoice_date',
                        faker()->dateTimeBetween(now()->startOfYear(), now()->endOfYear())
                    )
                        ->getSerialNumber('invoice_number', $order->client_id)
                        ->calculateBalance()
                        ->save();
                }
            }
        }
    }
}
