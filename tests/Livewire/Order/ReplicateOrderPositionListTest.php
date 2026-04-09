<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\ReplicateOrderPositionList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use Livewire\Livewire;
use function Livewire\invade;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $this->testOrder = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(ReplicateOrderPositionList::class, ['orderId' => $this->testOrder->getKey()])
        ->assertOk();
});

test('getResultFromQuery returns data key with positions', function (): void {
    $vatRate = VatRate::factory()->create();

    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'ReplicateTestPosition',
        'amount' => 5,
        'signed_amount' => 5,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 500,
        'total_gross_price' => 595,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
        ])->instance()
    );

    $query = $instance->getBuilder(OrderPosition::query());
    $result = $instance->getResultFromQuery($query);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('data')
        ->and($result['data'])->not->toBeEmpty();
});

test('free text positions are included in result', function (): void {
    $vatRate = VatRate::factory()->create();

    // Free text (simple, no children)
    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'FreeTextNote',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
        ])->instance()
    );

    $result = $instance->getResultFromQuery($instance->getBuilder(OrderPosition::query()));
    $names = collect($result['data'])->pluck('name');

    expect($names)->toContain('FreeTextNote');
});

test('block headers are included when they have remaining children', function (): void {
    $vatRate = VatRate::factory()->create();

    // Block header (free text with children)
    $block = OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'BlockHeader',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    // Child position inside the block
    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'ChildPosition',
        'amount' => 3,
        'signed_amount' => 3,
        'unit_net_price' => 50,
        'unit_gross_price' => 59.50,
        'total_net_price' => 150,
        'total_gross_price' => 178.50,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    // Verify the tree structure loads correctly
    $query = resolve_static(OrderPosition::class, 'familyTree')
        ->where('order_id', $this->testOrder->getKey())
        ->whereNull('parent_id');
    $tree = to_flat_tree($query->get()->toArray());
    $treeNames = collect($tree)->pluck('name')->toArray();

    // Debug: ensure familyTree loads the block with its child
    expect($treeNames)->toContain('BlockHeader')
        ->and($treeNames)->toContain('ChildPosition');

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
        ])->instance()
    );

    $result = $instance->getResultFromQuery($instance->getBuilder(OrderPosition::query()));
    $names = collect($result['data'])->pluck('name');

    expect($names)->toContain('BlockHeader')
        ->and($names)->toContain('ChildPosition');
});

test('positions in alreadyTakenPositions are excluded', function (): void {
    $vatRate = VatRate::factory()->create();

    $position = OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'AlreadyTaken',
        'amount' => 5,
        'signed_amount' => 5,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 500,
        'total_gross_price' => 595,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
            'alreadyTakenPositions' => [$position->getKey()],
        ])->instance()
    );

    $result = $instance->getResultFromQuery($instance->getBuilder(OrderPosition::query()));
    $names = collect($result['data'])->pluck('name');

    expect($names)->not->toContain('AlreadyTaken');
});

test('block with only free text children remaining is removed', function (): void {
    $vatRate = VatRate::factory()->create();

    $block = OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'BlockOnlyFreeTextKids',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    // Real child — fully taken (signed_amount=0)
    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'TakenChild',
        'amount' => 3,
        'signed_amount' => 0,
        'unit_net_price' => 50,
        'unit_gross_price' => 59.50,
        'total_net_price' => 150,
        'total_gross_price' => 178.50,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    // Free text child still in tree — but doesn't count as "real" child
    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'FreeTextInBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
        ])->instance()
    );

    $result = $instance->getResultFromQuery($instance->getBuilder(OrderPosition::query()));
    $names = collect($result['data'])->pluck('name');

    expect($names)->not->toContain('BlockOnlyFreeTextKids')
        ->and($names)->not->toContain('TakenChild');
});

test('free text inside kept block is included', function (): void {
    $vatRate = VatRate::factory()->create();

    $block = OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'KeptBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    // Free text child
    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'NoteInsideBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    // Real child — available
    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'RealChild',
        'amount' => 2,
        'signed_amount' => 2,
        'unit_net_price' => 80,
        'unit_gross_price' => 95.20,
        'total_net_price' => 160,
        'total_gross_price' => 190.40,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
        ])->instance()
    );

    $result = $instance->getResultFromQuery($instance->getBuilder(OrderPosition::query()));
    $names = collect($result['data'])->pluck('name');

    expect($names)->toContain('KeptBlock')
        ->and($names)->toContain('NoteInsideBlock')
        ->and($names)->toContain('RealChild');
});

test('taken free text positions are excluded from left list', function (): void {
    $vatRate = VatRate::factory()->create();

    $freeText = OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'AlreadyTakenFreeText',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
            'alreadyTakenPositions' => [$freeText->getKey()],
        ])->instance()
    );

    $result = $instance->getResultFromQuery($instance->getBuilder(OrderPosition::query()));
    $names = collect($result['data'])->pluck('name');

    expect($names)->not->toContain('AlreadyTakenFreeText');
});

test('updateAlreadyTakenPositions event updates state and clears selection', function (): void {
    Livewire::test(ReplicateOrderPositionList::class, [
        'orderId' => $this->testOrder->getKey(),
    ])
        ->assertSet('alreadyTakenPositions', [])
        ->dispatch('updateAlreadyTakenPositions', alreadyTakenPositions: [999])
        ->assertSet('alreadyTakenPositions', [999])
        ->assertSet('selected', []);
});

test('empty order returns empty data', function (): void {
    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
        ])->instance()
    );

    $result = $instance->getResultFromQuery($instance->getBuilder(OrderPosition::query()));

    expect($result)->toHaveKey('data')
        ->and($result['data'])->toBeEmpty();
});

test('block headers are removed when no children remain', function (): void {
    $vatRate = VatRate::factory()->create();

    // Block header with a child that has signed_amount=0 (fully taken)
    $block = OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'EmptyBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'FullyTakenChild',
        'amount' => 3,
        'signed_amount' => 0,
        'unit_net_price' => 50,
        'unit_gross_price' => 59.50,
        'total_net_price' => 150,
        'total_gross_price' => 178.50,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
        ])->instance()
    );

    $result = $instance->getResultFromQuery($instance->getBuilder(OrderPosition::query()));
    $names = collect($result['data'])->pluck('name');

    expect($names)->not->toContain('EmptyBlock')
        ->and($names)->not->toContain('FullyTakenChild');
});
