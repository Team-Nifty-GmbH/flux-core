<?php

namespace FluxErp\Tests\Livewire\Order;

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
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrderPositionsTest extends BaseSetup
{
    protected string $livewireComponent = OrderPositions::class;

    protected OrderForm $orderForm;

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

        $this->orderForm = new OrderForm(Livewire::new(OrderPositions::class), 'order');
        $this->orderForm->fill($this->order);
    }

    public function test_renders_successfully()
    {
        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->assertStatus(200);
    }

    public function test_can_delete_order_position()
    {
        $orderPosition = $this->order->orderPositions->first();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
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
        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->set('selected', ['*'])
            ->call('recalculateOrderPositions')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs(<<<'JS'
                $wire.$parent.recalculateOrderTotals();
            JS);
    }

    public function test_can_show_related_columns()
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);

        $component->set('enabledCols', array_merge($component->get('enabledCols'), ['order.uuid']))
            ->call('loadData')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertContains('order.uuid', $component->get('enabledCols'));
        $this->assertArrayHasKey('order.uuid', $component->get('data.data.0'));
        $this->assertEquals($this->order->uuid, $component->get('data.data.0')['order.uuid']);
    }

    public function test_quick_add_order_position()
    {
        $this->order->priceList->update(['is_net' => false]);
        $product = Product::factory()
            ->for(VatRate::factory())
            ->has(
                Price::factory()->state(['price_list_id' => $this->order->price_list_id]),
                'prices'
            )
            ->create();
        $orderPositionCount = $this->order->orderPositions()->count();
        /** @var Price $productPrice */
        $productPrice = $product->prices->first();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->set('orderPosition.product_id', $product->id)
            ->call('changedProductId', $product->id)
            ->assertSet(
                'orderPosition.unit_price',
                $grossPrice = $productPrice->getGross($product->vatRate->rate_percentage)
            )
            ->assertNotSet(
                'orderPosition.unit_price',
                $netPrice = $productPrice->getNet($product->vatRate->rate_percentage)
            )
            ->assertSet('orderPosition.is_net', false)
            ->call('quickAdd')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $this->assertEquals($orderPositionCount + 1, $this->order->orderPositions()->count());
        $newOrderPosition = $this->order->orderPositions()->where('product_id', $product->id)->first();

        $this->assertNotEquals($netPrice, $grossPrice);
        $this->assertEquals($netPrice, $newOrderPosition->unit_net_price);
        $this->assertEquals($grossPrice, $newOrderPosition->unit_gross_price);
    }
}
