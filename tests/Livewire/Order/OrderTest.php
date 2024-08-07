<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\Order as OrderView;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class OrderTest extends BaseSetup
{
    use DatabaseTransactions;

    private Order $order;

    public function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient,
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient,
            'contact_id' => $contact->id,
        ]);

        $currency = Currency::factory()->create();

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()->create([
            'client_id' => $this->dbClient,
        ]);

        $priceList = PriceList::factory()->create();

        $this->order = Order::factory()->create([
            'client_id' => $this->dbClient,
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $address->id,
            'address_delivery_id' => $address->id,
            'is_locked' => false,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs()
    {
        $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);

        foreach (Livewire::new(OrderView::class)->getTabs() as $tab) {
            $component
                ->set('tab', $tab->component)
                ->assertStatus(200);

            if ($tab->isLivewireComponent) {
                $component->assertSeeLivewire($tab->component);
            }
        }
    }
}
