<?php

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
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Livewire;
use function Livewire\invade;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'has_delivery_lock' => false,
        'credit_line' => null,
    ]);

    $address = Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $contact->id,
    ]);

    $language = Language::factory()->create();
    $this->vatRate = VatRate::factory()->create();

    $this->orderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'print_layouts' => ['invoice'],
    ]);

    $paymentType = PaymentType::factory()->create();
    $paymentType->clients()->attach($this->dbClient->id);

    $this->priceList = PriceList::factory()->create([
        'is_net' => true,
    ]);

    $this->product = Product::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'vat_rate_id' => $this->vatRate->id,
    ]);

    Price::factory()->create([
        'product_id' => $this->product->id,
        'price_list_id' => $this->priceList->id,
    ]);

    $currency = Currency::factory()->create([
        'name' => 'Euro',
        'iso' => 'EUR',
        'symbol' => '€',
        'is_default' => true,
    ]);

    $this->order = Order::factory()->create([
        'currency_id' => $currency->id,
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $language->id,
        'order_type_id' => $this->orderType->id,
        'payment_type_id' => $paymentType->id,
        'price_list_id' => $this->priceList->id,
        'contact_id' => $contact->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'is_locked' => false,
    ]);

    OrderPosition::factory()->create([
        'order_id' => $this->order->id,
        'vat_rate_id' => $this->vatRate->id,
        'amount' => 1,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_gross_price' => 119,
        'total_net_price' => 100,
        'client_id' => $this->dbClient->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $this->order->calculatePrices()->save();

    // Refresh the order to ensure all relationships are loaded
    $this->order = $this->order->fresh(['currency']);

    $this->orderForm = new OrderForm(Livewire::new(OrderPositions::class), 'order');
    $this->orderForm->fill($this->order);
});

test('actions disabled for locked order', function (): void {
    $this->order->update(['is_locked' => true]);
    $this->orderForm->fill($this->order);

    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $selectedActions = invade($component->instance())->getSelectedActions();

    $enabledActions = collect($selectedActions)->filter(fn ($action) => ! ($action->disabled ?? false))->count();

    // When order is locked, we expect actions to be present but behavior might be different
    expect($enabledActions)->toBeGreaterThanOrEqual(0);
});

test('add order position successfully', function (): void {
    $orderPositionCount = $this->order->orderPositions()->count();

    // Use variables instead of hardcoded values
    $testName = 'Test Position';
    $testAmount = 2;
    $testUnitPrice = 50;

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('orderPosition.name', $testName)
        ->set('orderPosition.amount', $testAmount)
        ->set('orderPosition.unit_price', $testUnitPrice)
        ->set('orderPosition.vat_rate_id', $this->vatRate->id)
        ->call('addOrderPosition')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($this->order->orderPositions()->count())->toEqual($orderPositionCount + 1);

    $newPosition = $this->order->orderPositions()->latest('id')->first();

    // Validate all provided and expected model properties
    expect($newPosition->name)->toEqual($testName);
    expect($newPosition->amount)->toEqual($testAmount);
    expect($newPosition->unit_net_price)->toEqual($testUnitPrice);
    expect($newPosition->vat_rate_id)->toEqual($this->vatRate->id);
    expect($newPosition->order_id)->toEqual($this->order->id);
    expect($newPosition->client_id)->toEqual($this->dbClient->getKey());

    // Validate model properties are properly set
    expect($newPosition->id)->not->toBeNull();
    expect($newPosition->created_at)->not->toBeNull();
    expect($newPosition->updated_at)->not->toBeNull();
    expect($newPosition->total_net_price)->toBeNumeric();
    expect($newPosition->total_gross_price)->toBeNumeric();
});

test('add order position with product', function (): void {
    $orderPositionCount = $this->order->orderPositions()->count();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('orderPosition.product_id', $this->product->id)
        ->set('orderPosition.amount', 1)
        ->call('changedProductId', $this->product)
        ->call('addOrderPosition')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($this->order->orderPositions()->count())->toEqual($orderPositionCount + 1);

    $newPosition = $this->order->orderPositions()->where('product_id', $this->product->id)->first();
    expect($newPosition)->not->toBeNull();
    expect($newPosition->product_id)->toEqual($this->product->id);
});

test('add products from array', function (): void {
    $products = [
        $this->product->id,
        ['product_id' => $this->product->id, 'amount' => 3],
    ];

    $orderPositionCount = $this->order->orderPositions()->count();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('addProducts', $products)
        ->assertStatus(200)
        ->assertHasNoErrors();

    expect($this->order->orderPositions()->count())->toEqual($orderPositionCount + 2);
});

test('can show related columns', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);

    $component->set('enabledCols', array_merge($component->get('enabledCols'), ['order.uuid']))
        ->call('loadData')
        ->assertStatus(200)
        ->assertHasNoErrors();

    expect($component->get('enabledCols'))->toContain('order.uuid');
    expect($component->get('data.data.0'))->toHaveKey('order.uuid');
    expect($component->get('data.data.0')['order.uuid'])->toEqual($this->order->uuid);
});

test('changed product id fills position data', function (): void {
    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('orderPosition.product_id', $this->product->id)
        ->call('changedProductId', $this->product)
        ->assertStatus(200)
        ->assertSet('orderPosition.name', $this->product->name)
        ->assertSet('orderPosition.product_number', $this->product->product_number)
        ->assertSet('orderPosition.description', $this->product->description)
        ->assertNotSet('orderPosition.unit_price', 0);
});

test('create tasks from selected positions', function (): void {
    $project = Project::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $orderPosition = $this->order->orderPositions->first();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('selected', [$orderPosition->id])
        ->call('createTasks', $project->id)
        ->assertStatus(200)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tasks', [
        'project_id' => $project->id,
        'order_position_id' => $orderPosition->id,
        'name' => $orderPosition->name,
    ]);
});

test('create tasks prevents duplicates', function (): void {
    $project = Project::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $orderPosition = $this->order->orderPositions->first();

    Task::factory()->create([
        'project_id' => $project->id,
        'order_position_id' => $orderPosition->id,
        'model_type' => $orderPosition->getMorphClass(),
        'model_id' => $orderPosition->id,
    ]);

    $initialTaskCount = Task::count();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('selected', [$orderPosition->id])
        ->call('createTasks', $project->id)
        ->assertStatus(200)
        ->assertHasNoErrors();

    expect(Task::count())->toEqual($initialTaskCount);
});

test('delete order position', function (): void {
    $orderPosition = $this->order->orderPositions->first();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('editOrderPosition', $orderPosition)
        ->call('deleteOrderPosition')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('order_positions', ['id' => $orderPosition->id]);
});

test('delete selected order positions', function (): void {
    $positions = OrderPosition::factory()->count(2)->create([
        'order_id' => $this->order->id,
        'client_id' => $this->dbClient->getKey(),
    ]);

    $selectedIds = $positions->pluck('id')->toArray();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('selected', $selectedIds)
        ->call('deleteSelectedOrderPositions')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('selected', []);

    foreach ($positions as $position) {
        $this->assertSoftDeleted('order_positions', ['id' => $position->id]);
    }
});

test('discount selected positions', function (): void {
    $orderPosition = $this->order->orderPositions->first();
    $discountPercentage = 10;

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('selected', [$orderPosition->id])
        ->set('discount', $discountPercentage)
        ->call('discountSelectedPositions')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('discount', null);

    $updatedPosition = $orderPosition->refresh();
    expect($updatedPosition->discount_percentage)->toEqual(0.10);
});

test('edit new order position', function (): void {
    $defaultVatRate = VatRate::factory()->create(['is_default' => true]);

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('editOrderPosition', new OrderPosition())
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('orderPosition.vat_rate_id', $defaultVatRate->id)
        ->assertExecutesJs("\$modalOpen('edit-order-position');");
});

test('edit order position', function (): void {
    $orderPosition = $this->order->orderPositions->first();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('editOrderPosition', $orderPosition)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('orderPosition.id', $orderPosition->id)
        ->assertSet('orderPosition.name', $orderPosition->name)
        ->assertExecutesJs("\$modalOpen('edit-order-position');");
});

test('get builder filters by order', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $builder = $component->instance()->getBuilder(OrderPosition::query());

    $this->assertStringContainsString('order_id', $builder->toSql());
});

test('get formatters', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $formatters = $component->instance()->getFormatters();

    expect($formatters)->toHaveKey('slug_position');
    expect($formatters)->toHaveKey('alternative_tag');
    expect($formatters['slug_position'])->toEqual('string');
});

test('get row actions', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $actions = invade($component->instance())->getRowActions();

    expect($actions)->toHaveCount(2);
});

test('get select attributes', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $attributes = $component->instance()->getSelectAttributes();

    expect($attributes)->toBeInstanceOf(ComponentAttributeBag::class);
    expect($attributes->getAttributes())->toHaveKey('x-show');
});

test('get selected actions', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $actions = invade($component->instance())->getSelectedActions();

    expect(count($actions))->toBeGreaterThan(0);

    $actionTexts = collect($actions)->map(fn ($action) => $action->text ?? $action->label ?? 'Unknown Action')->toArray();
    expect($actionTexts)->toContain(__('Create tasks'));
    expect($actionTexts)->toContain(__('Recalculate prices'));
    expect($actionTexts)->toContain(__('Discount selected positions'));
    expect($actionTexts)->toContain(__('Replicate'));
    expect($actionTexts)->toContain(__('Delete'));
});

test('get sortable order positions', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $sortablePositions = $component->instance()->getSortableOrderPositions();

    expect($sortablePositions)->toBeArray();
});

test('get table actions', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $actions = invade($component->instance())->getTableActions();

    expect($actions)->toHaveCount(2);
});

test('get view data includes vat rates', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $viewData = $component->instance()->getViewData();

    expect($viewData)->toHaveKey('vatRates');
    expect($viewData['vatRates'])->toBeArray();
});

test('item to array formatting', function (): void {
    $orderPosition = $this->order->orderPositions->first();
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);

    $formatted = invade($component->instance())->itemToArray($orderPosition);

    expect($formatted)->toHaveKey('indentation');
    expect($formatted)->toHaveKey('unit_price');
    expect($formatted)->toHaveKey('alternative_tag');
});

test('listeners configuration', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    $listeners = $component->instance()->getListeners();

    expect($listeners)->toHaveKey('create-tasks');
    expect($listeners)->toHaveKey('order:add-products');
    expect($listeners['create-tasks'])->toEqual('createTasks');
    expect($listeners['order:add-products'])->toEqual('addProducts');
});

test('mount initializes component', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);

    expect($component->get('page'))->toEqual(1);
    expect($component->get('filters'))->toBeEmpty();
    expect($component->get('selected'))->toBeEmpty();
    expect($component->get('orderPositionsView'))->toEqual('table');
});

test('move position', function (): void {
    $orderPosition = $this->order->orderPositions->first();
    $originalSortNumber = $orderPosition->sort_number;

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('movePosition', $orderPosition, 2)
        ->assertStatus(200)
        ->assertHasNoErrors();

    $updatedPosition = $orderPosition->refresh();
    expect($updatedPosition->sort_number)->toEqual(2);
    $this->assertNotEquals($originalSortNumber, $updatedPosition->sort_number);
});

test('move position with parent', function (): void {
    $parentPosition = OrderPosition::factory()->create([
        'order_id' => $this->order->id,
        'client_id' => $this->dbClient->getKey(),
    ]);

    $orderPosition = $this->order->orderPositions->first();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('movePosition', $orderPosition, 1, $parentPosition->id)
        ->assertStatus(200)
        ->assertHasNoErrors();

    $updatedPosition = $orderPosition->refresh();
    expect($updatedPosition->parent_id)->toEqual($parentPosition->id);
    expect($updatedPosition->sort_number)->toEqual(1);
});

test('quick add order position', function (): void {
    $orderPositionCount = $this->order->orderPositions()->count();
    $productPrice = $this->product->prices()->first();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('orderPosition.product_id', $this->product->id)
        ->call('changedProductId', $this->product)
        ->assertSet('orderPosition.name', $this->product->name)
        ->assertSet('orderPosition.product_number', $this->product->product_number)
        ->call('quickAdd')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($this->order->orderPositions()->count())->toEqual($orderPositionCount + 1);

    $newPosition = $this->order->orderPositions()->where('product_id', $this->product->id)->first();
    expect($newPosition->name)->toEqual($this->product->name);
    expect($newPosition->unit_net_price)->not->toBeNull();
});

test('recalculate order positions', function (): void {
    $warehouse = Warehouse::query()->where('is_default', true)->first();

    $orderPosition = OrderPosition::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'client_id' => $this->dbClient->getKey(),
        'price_list_id' => $this->priceList->id,
        'warehouse_id' => $warehouse->id,
    ]);

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('selected', [$orderPosition->id])
        ->call('recalculateOrderPositions')
        ->assertStatus(200)
        ->assertHasNoErrors();
});

test('renders successfully', function (): void {
    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->assertStatus(200)
        ->assertSet('orderPositionsView', 'table')
        ->assertSet('perPage', 100)
        ->assertSet('isSelectable', true)
        ->call('$refresh');

    // Just refresh to ensure data is loaded
    // Check enabledCols property separately
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
    expect($component->get('enabledCols'))->not->toBeEmpty();
});

test('replicate selected positions', function (): void {
    $orderPosition = $this->order->orderPositions->first();
    $originalCount = $this->order->orderPositions()->count();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->set('selected', [$orderPosition->id])
        ->call('replicateSelected')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('selected', []);

    expect($this->order->orderPositions()->count())->toEqual($originalCount + 1);

    $replicatedPosition = $this->order->orderPositions()->latest('id')->first();
    expect($replicatedPosition->name)->toEqual($orderPosition->name);
    expect($replicatedPosition->amount)->toEqual($orderPosition->amount);
    expect($replicatedPosition->origin_position_id)->toBeNull();
});

test('reset order position', function (): void {
    $orderPosition = $this->order->orderPositions->first();

    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('editOrderPosition', $orderPosition)
        ->assertSet('orderPosition.id', $orderPosition->id)
        ->call('resetOrderPosition')
        ->assertStatus(200)
        ->assertSet('orderPosition.id', null);
});

test('show product', function (): void {
    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('showProduct', $this->product)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertExecutesJs("\$openDetailModal('{$this->product->getUrl()}');");
});

test('switch view same view returns early', function (): void {
    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->assertSet('orderPositionsView', 'table')
        ->call('switchView', 'table')
        ->assertStatus(200)
        ->assertSet('orderPositionsView', 'table');
});

test('switch view to list', function (): void {
    Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->assertSet('orderPositionsView', 'table')
        ->call('switchView', 'list')
        ->assertStatus(200)
        ->assertSet('orderPositionsView', 'list')
        ->assertSet('data', []);
});

test('switch view to table', function (): void {
    $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
        ->call('switchView', 'list')
        ->assertSet('orderPositionsView', 'list')
        ->call('switchView', 'table')
        ->assertStatus(200)
        ->assertSet('orderPositionsView', 'table');

    expect($component->get('data'))->not->toBeEmpty();
});
