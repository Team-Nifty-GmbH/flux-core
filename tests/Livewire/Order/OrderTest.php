<?php

use FluxErp\Actions\ContactBankConnection\CalculateContactBankConnectionBalance;
use FluxErp\Enums\CreditAccountPostingEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Livewire\Order\Order as OrderView;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Discount;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use FluxErp\Models\VatRate;
use FluxErp\States\Order\DeliveryState\Delivered;
use FluxErp\States\Order\Open;
use FluxErp\States\Order\PaymentState\Paid;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
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

    $this->orderType = OrderType::factory()
        ->has(EmailTemplate::factory()->state(['model_type', 'order']), 'emailTemplate')
        ->create([
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
});

test('address update events', function (): void {
    $newAddress = Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $this->contact->id,
    ]);

    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->set('order.address_delivery_id', $newAddress->id)
        ->assertOk()
        ->assertSet('order.address_delivery_id', $newAddress->id);

    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->set('order.address_invoice_id', $newAddress->id)
        ->assertOk()
        ->assertSet('order.address_invoice_id', $newAddress->id)
        ->assertSet('order.contact_id', $newAddress->contact_id)
        ->assertSet('order.client_id', $newAddress->client_id);
});

test('create and manage discount', function (): void {
    $discountName = 'Test Discount';

    $component = Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->assertSet('order.discounts', [])
        ->call('editDiscount')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSee('edit-discount')
        ->assertSet('discount.is_percentage', true)
        ->set('discount.name', $discountName)
        ->set('discount.discount', 10)
        ->call('saveDiscount')
        ->assertOk()
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
        ->assertOk()
        ->assertSet('discount.id', $discount->id)
        ->assertSet('discount.name', $discountName)
        ->set('discount.discount', 15)
        ->call('saveDiscount')
        ->assertOk()
        ->assertReturned(true);

    $component
        ->call('deleteDiscount', $discount)
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('discounts', ['id' => $discount->id]);
});

test('create documents', function (): void {
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
        ->assertOk()
        ->assertHasNoErrors()
        ->assertNotSet('order.invoice_number', null);

    $invoice = $this->order->invoice();
    expect($invoice?->getPath())->not->toBeNull();
});

test('create documents with delivery lock fails', function (): void {
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
        ->assertOk()
        ->assertReturned(null)
        ->assertHasErrors(['has_contact_delivery_lock', 'balance'])
        ->assertSet('order.invoice_number', null);

    expect($this->order->refresh()->invoice_number)->toBeNull();
});

test('delete locked order fails', function (): void {
    $this->order->update(['is_locked' => true]);

    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->call('delete')
        ->assertOk()
        ->assertNoRedirect()
        ->assertHasErrors(['is_locked']);
});

test('delete order successful', function (): void {
    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->call('delete')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertRedirectToRoute('orders.orders');

    $this->assertSoftDeleted('orders', ['id' => $this->order->id]);
    $this->assertSoftDeleted('order_positions', ['order_id' => $this->order->id]);
});

test('fetch contact data', function (): void {
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
        ->assertOk()
        ->assertSet('order.client_id', $newContact->client_id)
        ->assertSet('order.address_invoice_id', $newContact->invoice_address_id)
        ->assertSet('order.address_delivery_id', $newContact->delivery_address_id);
});

test('get additional model actions', function (): void {
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

    expect($actions)->not->toBeEmpty();
    expect($actions)->toHaveCount(2);

    $this->order->update(['invoice_date' => null]);

    OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::SplitOrder,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $actions = $component->instance()->getAdditionalModelActions();
    expect($actions)->not->toBeEmpty();
});

test('get tabs structure', function (): void {
    $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
    $tabs = $component->instance()->getTabs();

    expect($tabs)->toHaveCount(7);

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
        expect($tab->component)->toEqual($expectedTabs[$index]);
    }
});

test('mount initializes order data', function (): void {
    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->assertSet('order.id', $this->order->id)
        ->assertSet('order.client_id', $this->order->client_id)
        ->assertSet('order.contact_id', $this->order->contact_id)
        ->assertSet('order.order_type_id', $this->order->order_type_id)
        ->assertSet('order.is_locked', false)
        ->call('$refresh');

    // Refresh to ensure data is loaded
    // Test the properties are arrays by checking they exist and are not null
    $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
    expect($component->get('availableStates'))->toBeArray();
    expect($component->get('states'))->toBeArray();
    expect($component->get('paymentStates'))->toBeArray();
    expect($component->get('deliveryStates'))->toBeArray();
});

test('order confirmation toggle', function (): void {
    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->set('order.is_confirmed', true)
        ->assertOk()
        ->assertHasNoErrors();

    expect($this->order->refresh()->is_confirmed)->toBeTrue();
});

test('order position with credit account', function (): void {
    Storage::fake();

    Currency::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'has_delivery_lock' => false,
        'credit_line' => null,
    ]);

    $creditAccount = ContactBankConnection::factory()
        ->has(Transaction::factory()->state([
            'amount' => 1000,
            'currency_id' => Currency::first()->getKey(),
            'value_date' => now(),
            'purpose' => 'Initial balance',
        ]))
        ->create([
            'contact_id' => $contact->getKey(),
            'is_credit_account' => true,
        ]);

    CalculateContactBankConnectionBalance::make([
        'id' => $creditAccount->getKey(),
    ])
        ->validate()
        ->execute();
    $creditAccount->refresh();

    expect(1000.00)->toEqual($creditAccount->balance);

    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $currency = Currency::factory()->create();

    $order = Order::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'currency_id' => $currency->getKey(),
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => Address::factory()
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_id' => $contact->getKey(),
            ])
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'payment_type_id' => PaymentType::factory()->create()->getKey(),
        'is_locked' => false,
        'invoice_number' => null,
    ]);

    $orderPosition = OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'client_id' => $this->dbClient->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'credit_account_id' => $creditAccount->getKey(),
        'post_on_credit_account' => CreditAccountPostingEnum::Credit,
        'credit_amount' => 250.00,
        'amount' => 1,
        'unit_net_price' => 250.00,
        'unit_gross_price' => 297.50,
        'total_net_price' => 250.00,
        'total_gross_price' => 297.50,
        'total_base_net_price' => 250.00,
        'total_base_gross_price' => 297.50,
        'vat_price' => 47.50,
        'vat_rate_percentage' => 0.19,
        'is_alternative' => false,
        'is_free_text' => false,
    ]);

    $order->calculatePrices()->save();

    expect($orderPosition->credit_account_id)->not->toBeNull();
    expect($orderPosition->credit_account_id)->toEqual($creditAccount->id);
    expect($orderPosition->post_on_credit_account)->toEqual(CreditAccountPostingEnum::Credit);
    expect($orderPosition->credit_amount)->toEqual('250.00');

    expect($creditAccount->is_credit_account)->toBeTrue();
    expect($creditAccount->contact_id)->toEqual($contact->getKey());

    $initialBalance = $creditAccount->balance;

    $component = Livewire::test(OrderView::class, ['id' => $order->getKey()]);
    $componentId = strtolower($component->id());

    $component
        ->assertSet('order.invoice_number', null)
        ->call('openCreateDocumentsModal')
        ->assertExecutesJs("\$modalOpen('create-documents-$componentId')")
        ->set([
            'selectedPrintLayouts' => [
                'download' => ['invoice'],
            ],
        ])
        ->call('createDocuments')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertNotSet('order.invoice_number', null);

    $creditAccount->refresh();

    expect($creditAccount->balance)->toEqual(bcadd($initialBalance, '250.00', 2));
});

test('order position with credit account debit', function (): void {
    Storage::fake();

    Currency::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'has_delivery_lock' => false,
        'credit_line' => null,
    ]);

    $creditAccount = ContactBankConnection::factory()
        ->has(Transaction::factory()->state([
            'amount' => 1000,
            'currency_id' => Currency::first()->getKey(),
            'value_date' => now(),
            'purpose' => 'Initial balance',
        ]))
        ->create([
            'contact_id' => $contact->getKey(),
            'is_credit_account' => true,
        ]);

    CalculateContactBankConnectionBalance::make([
        'id' => $creditAccount->getKey(),
    ])
        ->validate()
        ->execute();
    $creditAccount->refresh();

    expect(1000.00)->toEqual($creditAccount->balance);

    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $currency = Currency::factory()->create();

    $order = Order::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'currency_id' => $currency->getKey(),
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => Address::factory()
            ->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_id' => $contact->getKey(),
            ])
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'payment_type_id' => PaymentType::factory()->create()->getKey(),
        'is_locked' => false,
        'invoice_number' => null,
    ]);

    $orderPosition = OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'client_id' => $this->dbClient->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'credit_account_id' => $creditAccount->getKey(),
        'post_on_credit_account' => CreditAccountPostingEnum::Debit,
        'credit_amount' => 150.00,
        'amount' => 1,
        'unit_net_price' => 150.00,
        'unit_gross_price' => 178.50,
        'total_net_price' => 150.00,
        'total_gross_price' => 178.50,
        'total_base_net_price' => 150.00,
        'total_base_gross_price' => 178.50,
        'vat_price' => 28.50,
        'vat_rate_percentage' => 0.19,
        'is_alternative' => false,
        'is_free_text' => false,
    ]);

    $order->calculatePrices()->save();

    expect($orderPosition->credit_account_id)->not->toBeNull();
    expect($orderPosition->credit_account_id)->toEqual($creditAccount->id);
    expect($orderPosition->post_on_credit_account)->toEqual(CreditAccountPostingEnum::Debit);
    expect($orderPosition->credit_amount)->toEqual('150.00');

    expect($creditAccount->is_credit_account)->toBeTrue();
    expect($creditAccount->contact_id)->toEqual($contact->getKey());

    $initialBalance = $creditAccount->balance;

    $component = Livewire::test(OrderView::class, ['id' => $order->getKey()]);
    $componentId = strtolower($component->id());

    $component
        ->assertSet('order.invoice_number', null)
        ->call('openCreateDocumentsModal')
        ->assertExecutesJs("\$modalOpen('create-documents-$componentId')")
        ->set([
            'selectedPrintLayouts' => [
                'download' => ['invoice'],
            ],
        ])
        ->call('createDocuments')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertNotSet('order.invoice_number', null);

    $creditAccount->refresh();

    expect($creditAccount->balance)->toEqual(bcsub($initialBalance, '150.00', 2));
});

test('recalculate order totals', function (): void {
    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->call('recalculateOrderTotals')
        ->assertOk()
        ->assertNotSet('order.total_net_price', 0)
        ->assertNotSet('order.total_gross_price', 0);
});

test('render view data', function (): void {
    $component = Livewire::test(OrderView::class, ['id' => $this->order->id]);
    $viewData = $component->instance()->render()->getData();

    expect($viewData)->toHaveKey('additionalModelActions');
    expect($viewData)->toHaveKey('priceLists');
    expect($viewData)->toHaveKey('paymentTypes');
    expect($viewData)->toHaveKey('languages');
    expect($viewData)->toHaveKey('clients');
    expect($viewData)->toHaveKey('frequencies');
    expect($viewData)->toHaveKey('contactBankConnections');
    expect($viewData)->toHaveKey('vatRates');

    expect($viewData['priceLists'])->toBeArray();
    expect($viewData['paymentTypes'])->toBeArray();
    expect($viewData['languages'])->toBeArray();
    expect($viewData['clients'])->toBeArray();
    expect($viewData['frequencies'])->toBeArray();
    expect($viewData['contactBankConnections'])->toBeArray();
    expect($viewData['vatRates'])->toBeArray();
});

test('renders subscription order view', function (): void {
    $subscriptionOrderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Subscription,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $this->order->update(['order_type_id' => $subscriptionOrderType->id]);

    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->assertOk()
        ->assertViewIs('flux::livewire.order.subscription');
});

test('renders successfully', function (): void {
    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->assertOk()
        ->assertViewIs('flux::livewire.order.order')
        ->assertSet('order.id', $this->order->id)
        ->assertSet('tab', 'order.order-positions');
});

test('reorder discount', function (): void {
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
        ->assertOk()
        ->assertReturned(true);

    expect($discount1->refresh()->order_column)->toEqual(2);
    expect($discount2->refresh()->order_column)->toEqual(1);
});

test('replicate order', function (): void {
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
        ->assertOk()
        ->assertHasNoErrors()
        ->assertExecutesJs("\$modalOpen('replicate-order')")
        ->call('saveReplicate')
        ->assertOk()
        ->assertHasNoErrors();

    $replicatedOrder = Order::query()->whereKey($component->get('replicateOrder.id'))->first();

    expect($replicatedOrder->orderPositions()->count())->toEqual($this->order->orderPositions()->count());
    expect($replicatedOrder->invoice_number)->toBeNull();
    expect($replicatedOrder->is_locked)->toBeFalse();
    expect($replicatedOrder->parent_id)->toBeNull();
    expect($replicatedOrder->created_from_id)->toEqual($this->order->id);
    $this->assertNotEquals($replicatedOrder->order_number, $this->order->order_number);
});

test('save locked order uses update locked action', function (): void {
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
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->order->refresh();
    expect($this->order->commission)->toEqual($newCommission);
    expect($this->order->invoice_number)->toEqual($originalInvoiceNumber);
});

test('save order updates successfully', function (): void {
    $newCommission = 'Updated commission';

    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->set('order.commission', $newCommission)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($this->order->refresh()->commission)->toEqual($newCommission);
});

test('save states', function (): void {
    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->set('order.state', Open::class)
        ->set('order.payment_state', Paid::class)
        ->set('order.delivery_state', Delivered::class)
        ->call('saveStates')
        ->assertOk()
        ->assertHasNoErrors();
});

test('subscription schedule functionality', function (): void {
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
        ->assertOk()
        ->assertHasNoErrors()
        ->assertNotSet('schedule.id', null);

    $this->assertDatabaseHas('schedules', [
        'class' => ProcessSubscriptionOrder::class,
        'type' => 'invokable',
        'parameters->orderId' => $this->order->id,
        'parameters->orderTypeId' => $targetOrderType->id,
        'is_active' => 1,
    ]);
});

test('switch tabs', function (): void {
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
});

test('toggle lock functionality', function (): void {
    Livewire::test(OrderView::class, ['id' => $this->order->id])
        ->assertSet('order.is_locked', false)
        ->call('toggleLock')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('order.is_locked', true);

    expect($this->order->refresh()->is_locked)->toBeTrue();
});

test('vat calculation prevents negative amounts', function (): void {
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
    expect($order->total_net_price)->toEqual('0.00');

    $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
    expect($vat19['total_net_price'])->toEqual('0.00');
    expect($vat19['total_vat_price'])->toEqual('0.00');
});

test('vat calculation with combined discounts', function (): void {
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

    expect($order->total_net_price)->toEqual('37.50');

    // After 50% header: 19% = 25, 7% = 50, total = 75
    // After 37.50 flat: proportional distribution of remaining 37.50
    $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
    $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

    // 19% portion: 25/75 * 37.50 = 12.50
    expect($vat19['total_net_price'])->toEqual('12.50');
    expect($vat19['total_vat_price'])->toEqual('2.37');

    // 7% portion: 50/75 * 37.50 = 25.00
    expect($vat7['total_net_price'])->toEqual('25.00');
    expect($vat7['total_vat_price'])->toEqual('1.75');
});

test('vat calculation with flat header discount', function (): void {
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

    expect($order->total_net_price)->toEqual('112.50');

    // Check proportional distribution of flat discount
    $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
    $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

    // 19% portion: 50/150 * 112.50 = 37.50
    expect($vat19['total_net_price'])->toEqual('37.50');
    expect($vat19['total_vat_price'])->toEqual('7.12');

    // 7% portion: 100/150 * 112.50 = 75.00
    expect($vat7['total_net_price'])->toEqual('75.00');
    expect($vat7['total_vat_price'])->toEqual('5.25');
});

test('vat calculation with floating point precision', function (): void {
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
    expect($order->total_net_price)->toEqual('0.43');

    $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
    $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

    // Check that proportional distribution works correctly with bcmath
    // 19% group had 0.30 out of 0.60 = 50%
    // So they get 50% of 0.43 = 0.215
    expect($vat19['total_net_price'])->toEqual('0.22');
    // Rounded from 0.215
    expect($vat19['total_vat_price'])->toEqual('0.04');

    // 0.22 * 0.19 = 0.0418
    // 7% group had 0.30 out of 0.60 = 50%
    // So they get 50% of 0.43 = 0.215
    expect($vat7['total_net_price'])->toEqual('0.22');
    // Rounded from 0.215
    expect($vat7['total_vat_price'])->toEqual('0.02');
    // 0.22 * 0.07 = 0.0154
});

test('vat calculation with percentage header discount', function (): void {
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

    expect($order->total_net_price)->toEqual('75.00');

    // Check VAT calculations after header discount
    $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
    $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

    expect($vat19['total_net_price'])->toEqual('25.00');
    // 50 * 0.5
    expect($vat19['total_vat_price'])->toEqual('4.75');
    expect($vat7['total_net_price'])->toEqual('50.00');
    // 100 * 0.5
    expect($vat7['total_vat_price'])->toEqual('3.50');
});

test('vat calculation with position discounts', function (): void {
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

    expect($order->total_net_price)->toEqual('150.00');
    expect(count($order->total_vats))->toEqual(2);

    // Check individual VAT calculations
    $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
    $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');

    expect($vat19['total_net_price'])->toEqual('50.00');
    expect($vat19['total_vat_price'])->toEqual('9.50');
    expect($vat7['total_net_price'])->toEqual('100.00');
    expect($vat7['total_vat_price'])->toEqual('7.00');
});

test('order discount with mixed vat rates and position discounts', function (): void {
    $vatRate19 = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $vatRate7 = VatRate::factory()->create(['rate_percentage' => 0.07]);
    $vatRate0 = VatRate::factory()->create(['rate_percentage' => 0.00]);

    $order = Order::factory()
        ->has(OrderPosition::factory()
            ->for($vatRate19)
            ->state([
                'amount' => 1,
                'unit_net_price' => 2650,
                'unit_gross_price' => 3153.50,
                'total_base_net_price' => 2650,
                'total_base_gross_price' => 3153.50,
                'total_net_price' => 2385,
                'total_gross_price' => 2838.15,
                'discount_percentage' => 0.10,
                'vat_rate_percentage' => 0.19,
                'vat_price' => 453.15,
                'client_id' => $this->dbClient->getKey(),
                'is_alternative' => false,
            ])
        )
        ->has(OrderPosition::factory()
            ->for($vatRate7)
            ->state([
                'amount' => 2,
                'unit_net_price' => 100,
                'unit_gross_price' => 107,
                'total_base_net_price' => 200,
                'total_base_gross_price' => 214,
                'total_net_price' => 160,
                'total_gross_price' => 171.20,
                'discount_percentage' => 0.20,
                'vat_rate_percentage' => 0.07,
                'vat_price' => 11.20,
                'client_id' => $this->dbClient->getKey(),
                'is_alternative' => false,
            ])
        )
        ->has(OrderPosition::factory()
            ->for($vatRate0)
            ->state([
                'amount' => 1,
                'unit_net_price' => 500,
                'unit_gross_price' => 500,
                'total_base_net_price' => 500,
                'total_base_gross_price' => 500,
                'total_net_price' => 0,
                'total_gross_price' => 0,
                'discount_percentage' => 1.00,
                'vat_rate_percentage' => 0.00,
                'vat_price' => 0,
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

    $order->discounts()->create([
        'discount' => 0.10,
        'is_percentage' => true,
        'order_column' => 1,
    ]);

    $order->calculatePrices()->save();

    expect($order->total_net_price)->toEqual('2290.50');

    $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');
    $vat7 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0700000000');
    $vat0 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.0000000000');

    expect($vat19['total_net_price'])->toEqual('2146.50');
    expect($vat19['total_vat_price'])->toEqual('407.84');

    expect($vat7['total_net_price'])->toEqual('144.00');
    expect($vat7['total_vat_price'])->toEqual('10.08');

    expect($vat0['total_net_price'])->toEqual('0.00');
    expect($vat0['total_vat_price'])->toEqual('0.00');

    expect($order->total_gross_price)->toEqual('2708.42');
});

test('vat calculation with repeating decimals', function (): void {
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
    expect($order->total_net_price)->toEqual('66.67');

    $vat19 = collect($order->total_vats)->firstWhere('vat_rate_percentage', '0.1900000000');

    expect($vat19['total_net_price'])->toEqual('66.67');
    expect($vat19['total_vat_price'])->toEqual('12.67');
    // 66.67 * 0.19 = 12.6673
});
