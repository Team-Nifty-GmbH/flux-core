<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use FluxErp\Models\PriceList;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressAddressTypeOrderFactory extends Factory
{
    protected $model = AddressAddressTypeOrder::class;

    public function definition(): array
    {
        return [];
    }

    public function configure(): self
    {
        return $this->afterMaking(function (AddressAddressTypeOrder $pivot) {
            if (!isset($pivot->address_id)) {
                throw new \InvalidArgumentException(
                    'You must specify a client_id when creating an AddressAddressTypeOrder. ' .
                    'Use AddressAddressTypeOrder::factory()->forClient($clientId, $contactId)->create()'
                );
            }
        });
    }

    public function forClient(Client $client, int $contactId): self
    {
        return $this->state(function (array $attributes) use ($client, $contactId) {
            $address = Address::factory()->create([
                'client_id' => $client->id,
                'contact_id' => $contactId,
            ]);

            $addressType = AddressType::factory()->create([
                'client_id' => $client->id,
            ]);

            $currency = Currency::factory()->create([
                'is_default' => true,
            ]);

            $language = Language::factory()->create();

            $orderType = OrderType::factory()->create([
                'client_id' => $client->id,
                'order_type_enum' => OrderTypeEnum::Order,
            ]);


            $priceList = PriceList::factory()->create();

            $paymentType = PaymentType::factory()
                ->hasAttached(factory: $client, relationship: 'clients')
                ->create([
                    'is_default' => false,
                ]);

            $order = Order::factory()->create([
                'client_id' => $client->getKey(),
                'language_id' => $language->getKey(),
                'order_type_id' => $orderType->getKey(),
                'payment_type_id' => $paymentType->getKey(),
                'price_list_id' => $priceList->getKey(),
                'currency_id' => $currency->getKey(),
                'address_invoice_id' => $address->getKey(),
                'address_delivery_id' => $address->getKey(),
                'is_locked' => false,
            ]);

            return [
                'address_id' => $address->id,
                'address_type_id' => $addressType->id,
                'order_id' => $order->id,
            ];
        });
    }
}
