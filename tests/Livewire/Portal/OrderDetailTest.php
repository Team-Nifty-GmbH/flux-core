<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Portal\OrderDetail;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class OrderDetailTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $orders;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);

        $currency = Currency::factory()->create();

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $priceList = PriceList::factory()->create();

        $this->orders = Order::factory()
            ->count(1)
            ->hasOrderPositions(
                1,
                function ($attributes, $order) {
                    return [
                        'client_id' => $order->client_id,
                        'name' => 'test orderposition',
                    ];
                }
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $language->id,
                'order_type_id' => $orderType->id,
                'payment_type_id' => $paymentType->id,
                'price_list_id' => $priceList->id,
                'currency_id' => $currency->id,
                'contact_id' => $this->contact->id,
                'address_invoice_id' => $this->address->id,
                'address_delivery_id' => $this->address->id,
                'is_locked' => true,
            ]);

        $this->orders[] = Order::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'contact_id' => $contact->id,
            'address_invoice_id' => $address->id,
            'address_delivery_id' => $address->id,
            'is_locked' => true,
        ]);

        Order::addGlobalScope('portal', function ($query) {
            $query->where('contact_id', auth()->user()->contact_id)
                ->where(fn ($query) => $query->where('is_locked', true)
                    ->orWhere('is_imported', true)
                );
        });
    }

    public function test_renders_successfully()
    {
        Livewire::test(OrderDetail::class, ['id' => $this->orders[0]->id])
            ->assertStatus(200);
    }

    public function test_dont_render_order_from_other_address()
    {
        Livewire::test(OrderDetail::class, ['id' => $this->orders[1]->id])
            ->assertStatus(404);
    }

    public function test_select_order_position()
    {
        Livewire::test(OrderDetail::class, ['id' => $this->orders[0]->id])
            ->call('selectPosition', $this->orders[0]->orderPositions->first()->id)
            ->assertSet('positionDetails.id', $this->orders[0]->orderPositions->first()->id);
    }
}
