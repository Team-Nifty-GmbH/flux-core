<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Livewire\Order\Order as OrderView;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Discount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use FluxErp\States\Order\DeliveryState\Delivered;
use FluxErp\States\Order\Open;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

class OrderTest extends BaseSetup
{
    private Order $order;

    private OrderType $orderType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'has_delivery_lock' => false,
            'credit_line' => null,
        ]);

        $this->address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $this->contact->id,
        ]);

        ContactBankConnection::factory()->create([
            'contact_id' => $this->contact->id,
        ]);

        $currency = Currency::factory()->create();
        $vatRate = VatRate::factory()->create();

        $this->orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
            'print_layouts' => ['invoice'],
            'mail_subject' => 'Test Order {{ $order->order_number }}',
            'mail_body' => '<p>Test order body content</p>',
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $priceList = PriceList::factory()->create();

        $this->order = Order::factory()
            ->has(OrderPosition::factory()
                ->for($vatRate)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'unit_gross_price' => 119,
                    'total_gross_price' => 119,
                    'total_base_gross_price' => 119,
                    'total_net_price' => 100,
                    'total_base_net_price' => 100,
                    'client_id' => $this->dbClient->getKey(),
                    'is_free_text' => false,
                    'is_alternative' => false,
                ])
            )
            ->for($currency)
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'payment_type_id' => $paymentType->id,
                'price_list_id' => $priceList->id,
                'contact_id' => $this->contact->id,
                'address_invoice_id' => $this->address->id,
                'address_delivery_id' => $this->address->id,
                'is_locked' => false,
            ]);

        $this->order->calculatePrices()->save();
    }

    public function test_address_update_events(): void
    {
        $newAddress = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $this->contact->id,
        ]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set('order.address_delivery_id', $newAddress->id)
            ->assertStatus(200)
            ->assertSet('order.address_delivery_id', $newAddress->id);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set('order.address_invoice_id', $newAddress->id)
            ->assertStatus(200)
            ->assertSet('order.address_invoice_id', $newAddress->id)
            ->assertSet('order.contact_id', $newAddress->contact_id)
            ->assertSet('order.client_id', $newAddress->client_id);
    }

    public function test_create_and_manage_discount(): void
    {
        $discountName = 'Test Discount';

        $component = Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('order.discounts', [])
            ->call('editDiscount')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSee('edit-discount')
            ->assertSet('discount.is_percentage', true)
            ->set('discount.name', $discountName)
            ->set('discount.discount', 10)
            ->call('saveDiscount')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true)
            ->assertNotSet('order.discounts', []);

        $this->assertDatabaseHas('discounts', [
            'model_type' => 'order',
            'model_id' => $this->order->id,
            'name' => $discountName,
            'discount' => bcdiv(10, 100),
            'order_column' => 1,
            'is_percentage' => 1,
        ]);

        $discount = Discount::where('model_id', $this->order->id)->first();

        $component
            ->call('editDiscount', $discount)
            ->assertStatus(200)
            ->assertSet('discount.id', $discount->id)
            ->assertSet('discount.name', $discountName)
            ->set('discount.discount', 15)
            ->call('saveDiscount')
            ->assertStatus(200)
            ->assertReturned(true);

        $component
            ->call('deleteDiscount', $discount)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('discounts', ['id' => $discount->id]);
    }

    public function test_create_documents(): void
    {
        Storage::fake();

        $this->order->update(['is_locked' => false, 'invoice_number' => null]);

        $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
        $componentId = strtolower($component->id());

        $component
            ->assertSet('order.invoice_number', null)
            ->call('openCreateDocumentsModal')
            ->assertExecutesJs("\$modalOpen('create-documents-$componentId')")
            ->assertSet('printLayouts', [
                ['layout' => 'invoice', 'label' => 'invoice'],
            ])
            ->set([
                'selectedPrintLayouts' => [
                    'download' => ['invoice'],
                ],
            ])
            ->call('createDocuments')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertNotSet('order.invoice_number', null);

        $invoice = $this->order->invoice();
        $this->assertNotNull($invoice?->getPath());
    }

    public function test_create_documents_with_delivery_lock_fails(): void
    {
        $this->order->update(['is_locked' => false, 'invoice_number' => null]);
        $this->contact->update(['has_delivery_lock' => true, 'credit_line' => 1]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('order.invoice_number', null)
            ->call('openCreateDocumentsModal')
            ->set([
                'selectedPrintLayouts' => [
                    'download' => ['invoice'],
                ],
            ])
            ->call('createDocuments')
            ->assertStatus(200)
            ->assertReturned(null)
            ->assertHasErrors(['has_contact_delivery_lock', 'balance'])
            ->assertSet('order.invoice_number', null);

        $this->assertNull($this->order->refresh()->invoice_number);
    }

    public function test_delete_locked_order_fails(): void
    {
        $this->order->update(['is_locked' => true]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('delete')
            ->assertStatus(200)
            ->assertNoRedirect()
            ->assertHasErrors(['is_locked']);
    }

    public function test_delete_order_successful(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('delete')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertRedirectToRoute('orders.orders');

        $this->assertSoftDeleted('orders', ['id' => $this->order->id]);
        $this->assertSoftDeleted('order_positions', ['order_id' => $this->order->id]);
    }

    public function test_fetch_contact_data(): void
    {
        $newContact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $newAddress = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $newContact->id,
        ]);

        $newContact->update([
            'main_address_id' => $newAddress->id,
            'invoice_address_id' => $newAddress->id,
            'delivery_address_id' => $newAddress->id,
        ]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set('order.contact_id', $newContact->id)
            ->call('fetchContactData')
            ->assertStatus(200)
            ->assertSet('order.client_id', $newContact->client_id)
            ->assertSet('order.address_invoice_id', $newContact->invoice_address_id)
            ->assertSet('order.address_delivery_id', $newContact->delivery_address_id);
    }

    public function test_get_additional_model_actions(): void
    {
        $this->order->update([
            'invoice_date' => now(),
        ]);

        OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Retoure,
            'is_active' => true,
        ]);

        $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
        $actions = $component->instance()->getAdditionalModelActions();

        $this->assertNotEmpty($actions);
        $this->assertCount(2, $actions);

        $this->order->update(['invoice_date' => null]);

        OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::SplitOrder,
            'is_active' => true,
            'is_hidden' => false,
        ]);

        $actions = $component->instance()->getAdditionalModelActions();
        $this->assertNotEmpty($actions);
    }

    public function test_get_tabs_structure(): void
    {
        $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
        $tabs = $component->instance()->getTabs();

        $this->assertCount(7, $tabs);

        $expectedTabs = [
            'order.order-positions',
            'order.attachments',
            'order.texts',
            'order.accounting',
            'order.comments',
            'order.related',
            'order.activities',
        ];

        foreach ($tabs as $index => $tab) {
            $this->assertEquals($expectedTabs[$index], $tab->component);
        }
    }

    public function test_mount_initializes_order_data(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('order.id', $this->order->id)
            ->assertSet('order.client_id', $this->order->client_id)
            ->assertSet('order.contact_id', $this->order->contact_id)
            ->assertSet('order.order_type_id', $this->order->order_type_id)
            ->assertSet('order.is_locked', false)
            ->call('$refresh'); // Refresh to ensure data is loaded

        // Test the properties are arrays by checking they exist and are not null
        $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
        $this->assertIsArray($component->get('availableStates'));
        $this->assertIsArray($component->get('states'));
        $this->assertIsArray($component->get('paymentStates'));
        $this->assertIsArray($component->get('deliveryStates'));
    }

    public function test_order_confirmation_toggle(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set('order.is_confirmed', true)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertTrue($this->order->refresh()->is_confirmed);
    }

    public function test_recalculate_order_totals(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('recalculateOrderTotals')
            ->assertStatus(200)
            ->assertNotSet('order.total_net_price', 0)
            ->assertNotSet('order.total_gross_price', 0);
    }

    public function test_render_view_data(): void
    {
        $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
        $viewData = $component->instance()->render()->getData();

        $this->assertArrayHasKey('additionalModelActions', $viewData);
        $this->assertArrayHasKey('priceLists', $viewData);
        $this->assertArrayHasKey('paymentTypes', $viewData);
        $this->assertArrayHasKey('languages', $viewData);
        $this->assertArrayHasKey('clients', $viewData);
        $this->assertArrayHasKey('frequencies', $viewData);
        $this->assertArrayHasKey('contactBankConnections', $viewData);
        $this->assertArrayHasKey('vatRates', $viewData);

        $this->assertIsArray($viewData['priceLists']);
        $this->assertIsArray($viewData['paymentTypes']);
        $this->assertIsArray($viewData['languages']);
        $this->assertIsArray($viewData['clients']);
        $this->assertIsArray($viewData['frequencies']);
        $this->assertIsArray($viewData['contactBankConnections']);
        $this->assertIsArray($viewData['vatRates']);
    }

    public function test_renders_subscription_order_view(): void
    {
        $subscriptionOrderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Subscription,
            'is_active' => true,
            'is_hidden' => false,
        ]);

        $this->order->update(['order_type_id' => $subscriptionOrderType->id]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertStatus(200)
            ->assertViewIs('flux::livewire.order.subscription');
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertStatus(200)
            ->assertViewIs('flux::livewire.order.order')
            ->assertSet('order.id', $this->order->id)
            ->assertSet('tab', 'order.order-positions');
    }

    public function test_reorder_discount(): void
    {
        $discount1 = Discount::factory()->create([
            'model_type' => morph_alias(Order::class),
            'model_id' => $this->order->id,
            'order_column' => 1,
        ]);

        $discount2 = Discount::factory()->create([
            'model_type' => morph_alias(Order::class),
            'model_id' => $this->order->id,
            'order_column' => 2,
        ]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('reOrderDiscount', $discount1, 1) // Move to position 2
            ->assertStatus(200)
            ->assertReturned(true);

        $this->assertEquals(2, $discount1->refresh()->order_column);
        $this->assertEquals(1, $discount2->refresh()->order_column);
    }

    public function test_replicate_order(): void
    {
        OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
            'is_hidden' => false,
        ]);

        $this->order->update([
            'is_locked' => true,
            'invoice_number' => 'INV-' . Str::uuid(),
        ]);

        $component = Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('replicate')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs("\$modalOpen('replicate-order')")
            ->call('saveReplicate')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $replicatedOrder = Order::query()->whereKey($component->get('replicateOrder.id'))->first();

        $this->assertEquals($this->order->orderPositions()->count(), $replicatedOrder->orderPositions()->count());
        $this->assertNull($replicatedOrder->invoice_number);
        $this->assertFalse($replicatedOrder->is_locked);
        $this->assertNull($replicatedOrder->parent_id);
        $this->assertEquals($this->order->id, $replicatedOrder->created_from_id);
        $this->assertNotEquals($replicatedOrder->order_number, $this->order->order_number);
    }

    public function test_replicate_with_specific_order_type(): void
    {
        $retourOrderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Retoure,
            'is_active' => true,
        ]);

        $this->order->update([
            'is_locked' => true,
            'invoice_number' => 'INV-' . Str::uuid(),
            'invoice_date' => now(),
        ]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('replicate', OrderTypeEnum::Retoure->value)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs("\$modalOpen('create-child-order');")
            ->assertSet('replicateOrder.parent_id', $this->order->id)
            ->assertSet('replicateOrder.order_type_id', $retourOrderType->id);
    }

    public function test_save_locked_order_uses_update_locked_action(): void
    {
        $newCommission = 'Updated commission for locked order';
        $originalInvoiceNumber = 'INVOICE-123';

        $this->order->update([
            'is_locked' => true,
            'invoice_number' => $originalInvoiceNumber,
        ]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set('order.commission', $newCommission)
            ->set('order.invoice_number', 'SHOULD-NOT-CHANGE')
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $this->order->refresh();
        $this->assertEquals($newCommission, $this->order->commission);
        $this->assertEquals($originalInvoiceNumber, $this->order->invoice_number);
    }

    public function test_save_order_updates_successfully(): void
    {
        $newCommission = 'Updated commission';

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set('order.commission', $newCommission)
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $this->assertEquals($newCommission, $this->order->refresh()->commission);
    }

    public function test_save_states(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set('order.state', Open::class)
            ->set('order.payment_state', Paid::class)
            ->set('order.delivery_state', Delivered::class)
            ->call('saveStates')
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_subscription_schedule_functionality(): void
    {
        $subscriptionOrderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Subscription,
        ]);

        $targetOrderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
            'is_hidden' => false,
        ]);

        $this->order->update(['order_type_id' => $subscriptionOrderType->id]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set([
                'schedule.parameters.orderTypeId' => $targetOrderType->id,
                'schedule.parameters.orderId' => $this->order->id,
                'schedule.cron.methods.basic' => 'monthlyOn',
                'schedule.cron.parameters.basic' => ['1', '00:00', null],
            ])
            ->assertSet('schedule.id', null)
            ->call('saveSchedule')
            ->assertReturned(true)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertNotSet('schedule.id', null);

        $this->assertDatabaseHas('schedules', [
            'class' => ProcessSubscriptionOrder::class,
            'type' => 'invokable',
            'parameters->orderId' => $this->order->id,
            'parameters->orderTypeId' => $targetOrderType->id,
            'is_active' => 1,
        ]);
    }

    public function test_switch_tabs(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('tab', 'order.order-positions')
            ->set('tab', 'order.attachments')
            ->assertSet('tab', 'order.attachments')
            ->set('tab', 'order.texts')
            ->assertSet('tab', 'order.texts')
            ->set('tab', 'order.accounting')
            ->assertSet('tab', 'order.accounting')
            ->set('tab', 'order.comments')
            ->assertSet('tab', 'order.comments')
            ->set('tab', 'order.related')
            ->assertSet('tab', 'order.related')
            ->set('tab', 'order.activities')
            ->assertSet('tab', 'order.activities');
    }

    public function test_take_order_positions_functionality(): void
    {
        $orderPosition = $this->order->orderPositions()->first();

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('takeOrderPositions', [$orderPosition->id])
            ->assertStatus(200);
    }

    public function test_toggle_lock_functionality(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('order.is_locked', false)
            ->call('toggleLock')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('order.is_locked', true);

        $this->assertTrue($this->order->refresh()->is_locked);
    }

    public function test_vat_calculation_prevents_negative_amounts(): void
    {
        // Test that flat discounts don't create negative amounts
        $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);

        $order = Order::factory()
            ->has(OrderPosition::factory()
                ->for($vatRate19)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 100,
                    'vat_rate_percentage' => 0.19,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'currency_id' => Currency::factory()->create()->id,
                'contact_id' => $contact = Contact::factory()->create(['client_id' => $this->dbClient->getKey()])->id,
                'address_invoice_id' => Address::factory()->create(['client_id' => $this->dbClient->getKey(), 'contact_id' => $contact])->id,
                'price_list_id' => PriceList::factory()->create()->id,
                'payment_type_id' => PaymentType::factory()->create()->id,
                'shipping_costs_net_price' => 0,
                'shipping_costs_gross_price' => 0,
                'shipping_costs_vat_price' => 0,
            ]);

        // Add flat discount larger than order total
        $order->discounts()->create([
            'discount' => 150, // More than the 100 order total
            'is_percentage' => false,
            'order_column' => 1,
        ]);

        $order->calculatePrices()->save();

        // Should be 0, not negative
        $this->assertEquals('0.00', $order->total_net_price);

        $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
        $this->assertEquals('0.00', $vat19['total_net_price']);
        $this->assertEquals('0.00', $vat19['total_vat_price']);
    }

    public function test_vat_calculation_with_combined_discounts(): void
    {
        // Test complex scenario: position discount + percentage header + flat header
        $vatRate7 = VatRate::factory()->create(['rate_percentage' => 0.07]);
        $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);

        $order = Order::factory()
            ->has(OrderPosition::factory()
                ->for($vatRate19)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 50, // 50% position discount
                    'discount_percentage' => 0.5,
                    'vat_rate_percentage' => 0.19,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->has(OrderPosition::factory()
                ->for($vatRate7)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 100, // no position discount
                    'vat_rate_percentage' => 0.07,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'currency_id' => Currency::factory()->create()->id,
                'contact_id' => $contact = Contact::factory()->create(['client_id' => $this->dbClient->getKey()])->id,
                'address_invoice_id' => Address::factory()->create(['client_id' => $this->dbClient->getKey(), 'contact_id' => $contact])->id,
                'price_list_id' => PriceList::factory()->create()->id,
                'payment_type_id' => PaymentType::factory()->create()->id,
                'shipping_costs_net_price' => 0,
                'shipping_costs_gross_price' => 0,
                'shipping_costs_vat_price' => 0,
            ]);

        // Add 50% header discount first
        $order->discounts()->create([
            'discount' => 0.5,
            'is_percentage' => true,
            'order_column' => 1,
        ]);

        // Add 37.50 flat discount after percentage
        $order->discounts()->create([
            'discount' => 37.5,
            'is_percentage' => false,
            'order_column' => 2,
        ]);

        $order->calculatePrices()->save();

        $this->assertEquals('37.50', $order->total_net_price);

        // After 50% header: 19% = 25, 7% = 50, total = 75
        // After 37.50 flat: proportional distribution of remaining 37.50
        $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
        $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

        // 19% portion: 25/75 * 37.50 = 12.50
        $this->assertEquals('12.50', $vat19['total_net_price']);
        $this->assertEquals('2.37', $vat19['total_vat_price']);

        // 7% portion: 50/75 * 37.50 = 25.00
        $this->assertEquals('25.00', $vat7['total_net_price']);
        $this->assertEquals('1.75', $vat7['total_vat_price']);
    }

    public function test_vat_calculation_with_flat_header_discount(): void
    {
        // Create order with flat amount header discount
        $vatRate7 = VatRate::factory()->create(['rate_percentage' => 0.07]);
        $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);

        $order = Order::factory()
            ->has(OrderPosition::factory()
                ->for($vatRate19)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 50, // 50% position discount
                    'discount_percentage' => 0.5,
                    'vat_rate_percentage' => 0.19,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->has(OrderPosition::factory()
                ->for($vatRate7)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 100, // no position discount
                    'vat_rate_percentage' => 0.07,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'currency_id' => Currency::factory()->create()->id,
                'contact_id' => $contact = Contact::factory()->create(['client_id' => $this->dbClient->getKey()])->id,
                'address_invoice_id' => Address::factory()->create(['client_id' => $this->dbClient->getKey(), 'contact_id' => $contact])->id,
                'price_list_id' => PriceList::factory()->create()->id,
                'payment_type_id' => PaymentType::factory()->create()->id,
                'shipping_costs_net_price' => 0,
                'shipping_costs_gross_price' => 0,
                'shipping_costs_vat_price' => 0,
            ]);

        // Add 37.50 flat header discount (25% of 150)
        $order->discounts()->create([
            'discount' => 37.5,
            'is_percentage' => false,
            'order_column' => 1,
        ]);

        $order->calculatePrices()->save();

        $this->assertEquals('112.50', $order->total_net_price);

        // Check proportional distribution of flat discount
        $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
        $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

        // 19% portion: 50/150 * 112.50 = 37.50
        $this->assertEquals('37.50', $vat19['total_net_price']);
        $this->assertEquals('7.12', $vat19['total_vat_price']);

        // 7% portion: 100/150 * 112.50 = 75.00
        $this->assertEquals('75.00', $vat7['total_net_price']);
        $this->assertEquals('5.25', $vat7['total_vat_price']);
    }

    public function test_vat_calculation_with_floating_point_precision(): void
    {
        // Test with values that typically cause floating point issues
        $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);
        $vatRate7 = VatRate::factory()->create(['rate_percentage' => 0.07]);

        $order = Order::factory()
            ->has(OrderPosition::factory()
                ->for($vatRate19)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 0.1,  // Problematic floating point value
                    'total_base_net_price' => 0.1,
                    'total_net_price' => 0.1,
                    'vat_rate_percentage' => 0.19,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->has(OrderPosition::factory()
                ->for($vatRate19)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 0.2,  // Another problematic value
                    'total_base_net_price' => 0.2,
                    'total_net_price' => 0.2,
                    'vat_rate_percentage' => 0.19,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->has(OrderPosition::factory()
                ->for($vatRate7)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 0.3,  // 0.1 + 0.2 = 0.3 is a classic floating point problem
                    'total_base_net_price' => 0.3,
                    'total_net_price' => 0.3,
                    'vat_rate_percentage' => 0.07,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'currency_id' => Currency::factory()->create()->id,
                'contact_id' => $contact = Contact::factory()->create(['client_id' => $this->dbClient->getKey()])->id,
                'address_invoice_id' => Address::factory()->create(['client_id' => $this->dbClient->getKey(), 'contact_id' => $contact])->id,
                'price_list_id' => PriceList::factory()->create()->id,
                'payment_type_id' => PaymentType::factory()->create()->id,
                'shipping_costs_net_price' => 0,
                'shipping_costs_gross_price' => 0,
                'shipping_costs_vat_price' => 0,
            ]);

        // Add a flat discount that would cause rounding issues
        $order->discounts()->create([
            'discount' => 0.17,  // Discount that doesn't divide evenly
            'is_percentage' => false,
            'order_column' => 1,
        ]);

        $order->calculatePrices()->save();

        // Total should be 0.60 - 0.17 = 0.43
        $this->assertEquals('0.43', $order->total_net_price);

        $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
        $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

        // Check that proportional distribution works correctly with bcmath
        // 19% group had 0.30 out of 0.60 = 50%
        // So they get 50% of 0.43 = 0.215
        $this->assertEquals('0.22', $vat19['total_net_price']); // Rounded from 0.215
        $this->assertEquals('0.04', $vat19['total_vat_price']); // 0.22 * 0.19 = 0.0418

        // 7% group had 0.30 out of 0.60 = 50%
        // So they get 50% of 0.43 = 0.215
        $this->assertEquals('0.22', $vat7['total_net_price']); // Rounded from 0.215
        $this->assertEquals('0.02', $vat7['total_vat_price']); // 0.22 * 0.07 = 0.0154
    }

    public function test_vat_calculation_with_percentage_header_discount(): void
    {
        // Create order with header percentage discount
        $vatRate7 = VatRate::factory()->create(['rate_percentage' => 0.07]);
        $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);

        $order = Order::factory()
            ->has(OrderPosition::factory()
                ->for($vatRate19)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 50, // 50% position discount
                    'discount_percentage' => 0.5,
                    'vat_rate_percentage' => 0.19,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->has(OrderPosition::factory()
                ->for($vatRate7)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 100, // no position discount
                    'vat_rate_percentage' => 0.07,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'currency_id' => Currency::factory()->create()->id,
                'contact_id' => $contact = Contact::factory()->create(['client_id' => $this->dbClient->getKey()])->id,
                'address_invoice_id' => Address::factory()->create(['client_id' => $this->dbClient->getKey(), 'contact_id' => $contact])->id,
                'price_list_id' => PriceList::factory()->create()->id,
                'payment_type_id' => PaymentType::factory()->create()->id,
                'shipping_costs_net_price' => 0,
                'shipping_costs_gross_price' => 0,
                'shipping_costs_vat_price' => 0,
            ]);

        // Add 50% header discount
        $order->discounts()->create([
            'discount' => 0.5,
            'is_percentage' => true,
            'order_column' => 1,
        ]);

        $order->calculatePrices()->save();

        $this->assertEquals('75.00', $order->total_net_price);

        // Check VAT calculations after header discount
        $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
        $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

        $this->assertEquals('25.00', $vat19['total_net_price']); // 50 * 0.5
        $this->assertEquals('4.75', $vat19['total_vat_price']);
        $this->assertEquals('50.00', $vat7['total_net_price']); // 100 * 0.5
        $this->assertEquals('3.50', $vat7['total_vat_price']);
    }

    public function test_vat_calculation_with_position_discounts(): void
    {
        // Create order with mixed VAT rates and position discounts
        $vatRate7 = VatRate::factory()->create(['rate_percentage' => 0.07]);
        $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);

        $order = Order::factory()
            ->has(OrderPosition::factory()
                ->for($vatRate19)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 50, // 50% position discount
                    'discount_percentage' => 0.5,
                    'vat_rate_percentage' => 0.19,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->has(OrderPosition::factory()
                ->for($vatRate7)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 100, // no position discount
                    'vat_rate_percentage' => 0.07,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'currency_id' => Currency::factory()->create()->id,
                'contact_id' => $contact = Contact::factory()->create(['client_id' => $this->dbClient->getKey()])->id,
                'address_invoice_id' => Address::factory()->create(['client_id' => $this->dbClient->getKey(), 'contact_id' => $contact])->id,
                'price_list_id' => PriceList::factory()->create()->id,
                'payment_type_id' => PaymentType::factory()->create()->id,
                'shipping_costs_net_price' => 0,
                'shipping_costs_gross_price' => 0,
                'shipping_costs_vat_price' => 0,
            ]);

        $order->calculatePrices()->save();

        $this->assertEquals('150.00', $order->total_net_price);
        $this->assertEquals(2, count($order->total_vats));

        // Check individual VAT calculations
        $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
        $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

        $this->assertEquals('50.00', $vat19['total_net_price']);
        $this->assertEquals('9.50', $vat19['total_vat_price']);
        $this->assertEquals('100.00', $vat7['total_net_price']);
        $this->assertEquals('7.00', $vat7['total_vat_price']);
    }

    public function test_vat_calculation_with_repeating_decimals(): void
    {
        // Test with 1/3 values that create repeating decimals
        $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);

        $order = Order::factory()
            ->has(OrderPosition::factory()
                ->for($vatRate19)
                ->state([
                    'amount' => 1,
                    'unit_net_price' => 100,
                    'total_base_net_price' => 100,
                    'total_net_price' => 100,
                    'vat_rate_percentage' => 0.19,
                    'client_id' => $this->dbClient->getKey(),
                    'is_alternative' => false,
                ])
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'currency_id' => Currency::factory()->create()->id,
                'contact_id' => $contact = Contact::factory()->create(['client_id' => $this->dbClient->getKey()])->id,
                'address_invoice_id' => Address::factory()->create(['client_id' => $this->dbClient->getKey(), 'contact_id' => $contact])->id,
                'price_list_id' => PriceList::factory()->create()->id,
                'payment_type_id' => PaymentType::factory()->create()->id,
                'shipping_costs_net_price' => 0,
                'shipping_costs_gross_price' => 0,
                'shipping_costs_vat_price' => 0,
            ]);

        // Add percentage discount that creates repeating decimal (1/3)
        $order->discounts()->create([
            'discount' => 0.333333333,  // 33.3333333%
            'is_percentage' => true,
            'order_column' => 1,
        ]);

        $order->calculatePrices()->save();

        // 100 * (1 - 0.333333) = 66.6667, but bcround should round to 66.67
        $this->assertEquals('66.67', $order->total_net_price);

        $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');

        $this->assertEquals('66.67', $vat19['total_net_price']);
        $this->assertEquals('12.67', $vat19['total_vat_price']); // 66.67 * 0.19 = 12.6673
    }
}
