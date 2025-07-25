<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Livewire\Order\Order as OrderView;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
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

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'has_delivery_lock' => false,
            'credit_line' => null,
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);

        ContactBankConnection::factory()->create([
            'contact_id' => $contact->id,
        ]);

        $currency = Currency::factory()->create();

        $this->orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
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
                    'total_base_gross_price' => 119,
                    'total_net_price' => 100,
                    'total_base_net_price' => 100,
                    'client_id' => $this->dbClient->getKey(),
                    'is_free_text' => false,
                    'is_alternative' => false,
                ])
            )
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'language_id' => $this->defaultLanguage->id,
                'order_type_id' => $this->orderType->id,
                'payment_type_id' => $paymentType->id,
                'price_list_id' => $priceList->id,
                'currency_id' => $currency->id,
                'address_invoice_id' => $address->id,
                'address_delivery_id' => $address->id,
                'is_locked' => false,
            ]);

        $this->order->calculatePrices()->save();
    }

    public function test_add_schedule_to_order(): void
    {
        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Subscription,
        ]);
        $this->order->update(['order_type_id' => $orderType->id]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set([
                'schedule.parameters.orderTypeId' => $this->orderType->id,
                'schedule.parameters.orderId' => $this->order->id,
                'schedule.cron.methods.basic' => 'monthlyOn',
                'schedule.cron.parameters.basic' => ['1', '00:00', null],
            ])
            ->assertSet('schedule.id', null)
            ->assertSet('order.schedule_id', null)
            ->call('saveSchedule')
            ->assertReturned(true)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertNotSet('schedule.id', null);

        $this->assertDatabaseHas(
            'schedules',
            [
                'class' => ProcessSubscriptionOrder::class,
                'type' => 'invokable',
                'parameters->orderId' => $this->order->id,
                'parameters->orderTypeId' => $this->orderType->id,
                'cron_expression' => null,
                'due_at' => null,
                'last_success' => null,
                'last_run' => null,
                'is_active' => 1,
            ]
        );
    }

    public function test_can_add_discount(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('order.discounts', [])
            ->call('editDiscount')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs(<<<'JS'
                $modalOpen('edit-discount');
            JS)
            ->assertSet('discount.is_percentage', true)
            ->set('discount.name', $discountName = Str::uuid()->toString())
            ->set('discount.discount', 10)
            ->call('saveDiscount')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertNotSet('order.discounts', []);

        $this->assertDatabaseHas(
            'discounts',
            [
                'model_type' => 'order',
                'model_id' => $this->order->id,
                'name' => $discountName,
                'discount' => bcdiv(10, 100),
                'order_column' => 1,
                'is_percentage' => 1,
            ]
        );
    }

    public function test_can_delete_order(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('delete')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertRedirectToRoute('orders.orders');

        $this->assertSoftDeleted('orders', ['id' => $this->order->id]);
        $this->assertSoftDeleted('order_positions', ['order_id' => $this->order->id]);
    }

    public function test_can_render_subscription_order(): void
    {
        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Subscription,
            'is_active' => true,
            'is_hidden' => false,
        ]);
        $this->order->update([
            'order_type_id' => $orderType->id,
            'is_locked' => true,
            'invoice_number' => Str::uuid(),
        ]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertStatus(200)
            ->assertViewIs('flux::livewire.order.subscription')
            ->set('schedule.cron.parameters.basic.1', 1)
            ->assertStatus(200)
            ->assertViewIs('flux::livewire.order.subscription');
    }

    public function test_cant_create_invoice_with_delivery_lock(): void
    {
        $this->order->update(['is_locked' => false, 'invoice_number' => null]);
        $this->order->contact->update(['has_delivery_lock' => true, 'credit_line' => 1]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('order.invoice_number', null)
            ->call('openCreateDocumentsModal')
            ->assertSet(
                'printLayouts',
                [
                    [
                        'layout' => 'invoice',
                        'label' => 'invoice',
                    ],
                ]
            )
            ->set([
                'selectedPrintLayouts' => [
                    'download' => [
                        'invoice',
                    ],
                ],
            ])
            ->call('createDocuments')
            ->assertStatus(200)
            ->assertReturned(null)
            ->assertHasErrors(['has_contact_delivery_lock', 'balance'])
            ->assertSet('order.invoice_number', null);

        $this->assertNull($this->order->refresh()->invoice_number);
        $this->assertNull($this->order->invoice());
    }

    public function test_create_invoice(): void
    {
        $this->order->update(['is_locked' => false, 'invoice_number' => null]);
        Storage::fake();

        $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
        $componentId = strtolower($component->id());
        $component
            ->assertSet('order.invoice_number', null)
            ->call('openCreateDocumentsModal')
            ->assertExecutesJs(<<<JS
                \$modalOpen('create-documents-$componentId')
             JS)
            ->assertSet(
                'printLayouts',
                [
                    [
                        'layout' => 'invoice',
                        'label' => 'invoice',
                    ],
                ]
            )
            ->set([
                'selectedPrintLayouts' => [
                    'download' => [
                        'invoice',
                    ],
                ],
            ])
            ->call('createDocuments')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertNotSet('order.invoice_number', null);

        $invoice = $this->order->invoice();

        $this->assertNotNull($invoice?->getPath());
        $this->assertFileExists($invoice->getPath());
        $this->assertNotEmpty(file_get_contents($invoice->getPath()));
    }

    public function test_delete_locked_order_fails(): void
    {
        $this->order->update(['is_locked' => true]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('delete')
            ->assertStatus(200)
            ->assertNoRedirect()
            ->assertHasErrors(['is_locked'])
            ->assertToastNotification(type: 'error');
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertStatus(200);
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
            'invoice_number' => Str::uuid(),
        ]);

        $this->order->calculatePrices()->save();

        $component = Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('replicate')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs(<<<'JS'
                $modalOpen('replicate-order')
             JS)
            ->call('saveReplicate')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $component->assertRedirectToRoute(
            'orders.id',
            ['id' => $component->get('replicateOrder.id')]
        );
        $replicatedOrder = Order::query()
            ->whereKey($component->get('replicateOrder.id'))
            ->first();

        $this->assertEquals($this->order->orderPositions()->count(), $replicatedOrder->orderPositions()->count());
        $this->assertNull($replicatedOrder->invoice_number);
        $this->assertFalse($replicatedOrder->is_locked);
        $this->assertNull($replicatedOrder->parent_id);
        $this->assertEquals($this->order->id, $replicatedOrder->created_from_id);
        $this->assertNotEquals($replicatedOrder->order_number, $this->order->order_number);
        $this->assertNotEquals($replicatedOrder->uuid, $this->order->uuid);
        $this->assertEquals(
            0,
            $replicatedOrder->orderPositions()
                ->whereNull('created_from_id')
                ->count()
        );
    }

    public function test_replicate_with_new_contact(): void
    {
        $contact = Contact::factory()
            ->has(
                Address::factory()
                    ->for($this->dbClient)
                    ->state([
                        'is_main_address' => true,
                        'is_invoice_address' => true,
                        'is_delivery_address' => true,
                    ])
            )
            ->for(User::factory()->for($this->defaultLanguage), 'agent')
            ->for(PriceList::factory())
            ->for(PaymentType::factory()->hasAttached($this->dbClient))
            ->for($this->dbClient)
            ->afterCreating(
                fn (Contact $contact) => $contact->update([
                    'main_address_id' => $contact->addresses->first()->id,
                    'invoice_address_id' => $contact->addresses->first()->id,
                    'delivery_address_id' => $contact->addresses->first()->id,
                ])
            )
            ->create();

        $component = Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('replicate')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs(<<<'JS'
                $modalOpen('replicate-order')
             JS)
            ->set('replicateOrder.contact_id', $contact->getKey())
            ->call('fetchContactData', true)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('replicateOrder.contact_id', $contact->getKey())
            ->assertSet('replicateOrder.address_invoice_id', $contact->invoice_address_id)
            ->assertSet('replicateOrder.address_delivery_id', $contact->delivery_address_id)
            ->assertSet('replicateOrder.price_list_id', $contact->price_list_id)
            ->assertSet('replicateOrder.payment_type_id', $contact->payment_type_id)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->call('saveReplicate')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $replicatedOrder = Order::query()->whereKey($component->get('replicateOrder.id'))->first();
        $this->assertEquals($this->order->orderPositions()->count(), $replicatedOrder->orderPositions()->count());
        $this->assertNotEquals($replicatedOrder->agent_id, $this->order->agent_id);
        $this->assertNotEquals($replicatedOrder->contact_id, $this->order->contact_id);
        $this->assertNotEquals($replicatedOrder->address_invoice_id, $this->order->address_invoice_id);
        $this->assertNotEquals($replicatedOrder->address_delivery_id, $this->order->address_delivery_id);
        $this->assertNotEquals($replicatedOrder->order_number, $this->order->order_number);
        $this->assertNotEquals($replicatedOrder->uuid, $this->order->uuid);
        $this->assertNull($replicatedOrder->invoice_number);
        $this->assertFalse($replicatedOrder->is_locked);
        $this->assertNull($replicatedOrder->parent_id);
        $this->assertEquals($this->order->id, $replicatedOrder->created_from_id);
        $this->assertEquals(
            0,
            $replicatedOrder->orderPositions()
                ->whereNull('created_from_id')
                ->count()
        );
    }

    public function test_switch_tabs(): void
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])->cycleTabs();
    }

    public function test_update_locked_order(): void
    {
        $commission = Str::uuid()->toString();
        $invoiceNumber = $this->order->invoice_number;
        $this->order->update(['is_locked' => true]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->set('order.commission', $commission)
            ->set('order.invoice_number', $commission)
            ->assertSet('order.commission', $commission)
            ->assertSet('order.invoice_number', $commission)
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->order->refresh();

        // ensure that the commission changed but the invoice number didnt
        $this->assertTrue($this->order->is_locked);
        $this->assertEquals($commission, $this->order->commission);
        $this->assertEquals($invoiceNumber, $this->order->invoice_number);
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
