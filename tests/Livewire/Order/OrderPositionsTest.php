<?php

namespace Tests\Feature\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Order\OrderPositions;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrderPositionsTest extends BaseSetup
{
    protected string $livewireComponent = OrderPositions::class;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
            'has_delivery_lock' => false,
            'credit_line' => null,
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $contact->id,
        ]);

        $currency = Currency::factory()->create();

        $language = Language::factory()->create();

        $this->orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Order,
            'print_layouts' => ['invoice'],
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $priceList = PriceList::factory()->create();

        $this->order = Order::factory()
            ->has(OrderPosition::factory()
                ->for(VatRate::factory())
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'unit_gross_price' => 119,
                    'total_gross_price' => 119,
                    'total_net_price' => 100,
                    'client_id' => $this->dbClient->id,
                    'is_free_text' => false,
                    'is_alternative' => false,
                ])
            )
            ->for(Currency::factory())
            ->create([
                'client_id' => $this->dbClient->id,
                'language_id' => $language->id,
                'order_type_id' => $this->orderType->id,
                'payment_type_id' => $paymentType->id,
                'price_list_id' => $priceList->id,
                'address_invoice_id' => $address->id,
                'address_delivery_id' => $address->id,
                'is_locked' => false,
            ]);

        $this->order->calculatePrices()->save();
    }

    public function test_renders_successfully()
    {
        $form = new OrderForm(Livewire::new(OrderPositions::class), 'order');
        $form->fill($this->order);

        Livewire::test(OrderPositions::class, ['order' => $form])
            ->assertStatus(200);
    }

    public function test_can_delete_order_position()
    {
        $orderPosition = $this->order->orderPositions->first();
        $form = new OrderForm(Livewire::new(OrderPositions::class), 'order');
        $form->fill($this->order);

        Livewire::test(OrderPositions::class, ['order' => $form])
            ->call('editOrderPosition', $orderPosition->id)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('orderPosition.id', $orderPosition->id)
            ->assertExecutesJs(<<<'JS'
                $openModal('edit-order-position');
            JS)
            ->call('deleteOrderPosition')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs(<<<'JS'
                $wire.$parent.recalculateOrderTotals();
            JS);

        $this->assertSoftDeleted('order_positions', ['id' => $orderPosition->id]);
    }

    public function test_recalculate_prices()
    {
        $form = new OrderForm(Livewire::new(OrderPositions::class), 'order');
        $form->fill($this->order);

        Livewire::test(OrderPositions::class, ['order' => $form])
            ->set('selected', ['*'])
            ->call('recalculateOrderPositions')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs(<<<'JS'
                $wire.$parent.recalculateOrderTotals();
            JS);
    }
}
