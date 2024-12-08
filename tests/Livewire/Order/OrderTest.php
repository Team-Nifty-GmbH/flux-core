<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Livewire\Order\Order as OrderView;
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
            ->create([
                'client_id' => $this->dbClient->id,
                'language_id' => $language->id,
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

    public function test_update_locked_order()
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

    public function test_delete_locked_order_fails()
    {
        $this->order->update(['is_locked' => true]);

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('delete')
            ->assertStatus(200)
            ->assertNoRedirect()
            ->assertHasErrors(['is_locked'])
            ->assertWireuiNotification(icon: 'error');
    }

    public function test_add_schedule_to_order()
    {
        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
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

    public function test_create_invoice()
    {
        $this->order->update(['is_locked' => false, 'invoice_number' => null]);
        Storage::fake();

        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('order.invoice_number', null)
            ->call('openCreateDocumentsModal')
            ->assertExecutesJs(<<<'JS'
                $openModal('create-documents')
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

    public function test_cant_create_invoice_with_delivery_lock()
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

    public function test_replicate_order()
    {
        OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
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
            ->assertSet('replicateOrderTypes', [])
            ->assertExecutesJs(<<<'JS'
                $openModal('replicate-order')
             JS)
            ->call('saveReplicate')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $component->assertRedirectToRoute(
            'orders.id',
            ['id' => $component->get('replicateOrder.id')]
        );
        $replicatedOrder = Order::query()->whereKey($component->get('replicateOrder.id'))->first();

        $this->assertEquals($this->order->orderPositions()->count(), $replicatedOrder->orderPositions()->count());
        $this->assertEquals(null, $replicatedOrder->invoice_number);
        $this->assertEquals(false, $replicatedOrder->is_locked);
        $this->assertEquals($this->order->id, $replicatedOrder->parent_id);
        $this->assertNotEquals($replicatedOrder->order_number, $this->order->order_number);
        $this->assertNotEquals($replicatedOrder->uuid, $this->order->uuid);
        $this->assertEquals(
            0,
            $replicatedOrder->orderPositions()
                ->whereNotNull('origin_position_id')
                ->count()
        );
    }

    public function test_can_render_subscription_order()
    {
        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
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

    public function test_can_add_discount()
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->assertSet('order.discounts', [])
            ->call('editDiscount')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs(<<<'JS'
                $openModal('edit-discount');
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

    public function test_can_delete_order()
    {
        Livewire::test(OrderView::class, ['id' => $this->order->id])
            ->call('delete')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertRedirectToRoute('orders.orders');

        $this->assertSoftDeleted('orders', ['id' => $this->order->id]);
        $this->assertSoftDeleted('order_positions', ['order_id' => $this->order->id]);
    }
}
