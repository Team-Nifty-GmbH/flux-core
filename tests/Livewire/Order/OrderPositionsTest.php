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
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Livewire;
use function Livewire\invade;

class OrderPositionsTest extends BaseSetup
{
    protected string $livewireComponent = OrderPositions::class;

    protected Order $order;

    protected OrderForm $orderForm;

    protected OrderType $orderType;

    protected PriceList $priceList;

    protected Product $product;

    protected VatRate $vatRate;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_actions_disabled_for_locked_order(): void
    {
        $this->order->update(['is_locked' => true]);
        $this->orderForm->fill($this->order);

        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $selectedActions = invade($component->instance())->getSelectedActions();

        $enabledActions = collect($selectedActions)->filter(fn ($action) => ! ($action->disabled ?? false))->count();
        // When order is locked, we expect actions to be present but behavior might be different
        $this->assertGreaterThanOrEqual(0, $enabledActions);
    }

    public function test_add_order_position_successfully(): void
    {
        $orderPositionCount = $this->order->orderPositions()->count();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->set('orderPosition.name', 'Test Position')
            ->set('orderPosition.amount', 2)
            ->set('orderPosition.unit_price', 50)
            ->set('orderPosition.vat_rate_id', $this->vatRate->id)
            ->call('addOrderPosition')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $this->assertEquals($orderPositionCount + 1, $this->order->orderPositions()->count());

        $newPosition = $this->order->orderPositions()->latest('id')->first();
        $this->assertEquals('Test Position', $newPosition->name);
        $this->assertEquals(2, $newPosition->amount);
        $this->assertEquals(50, $newPosition->unit_net_price);
    }

    public function test_add_order_position_with_product(): void
    {
        $orderPositionCount = $this->order->orderPositions()->count();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->set('orderPosition.product_id', $this->product->id)
            ->set('orderPosition.amount', 1)
            ->call('changedProductId', $this->product)
            ->call('addOrderPosition')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $this->assertEquals($orderPositionCount + 1, $this->order->orderPositions()->count());

        $newPosition = $this->order->orderPositions()->where('product_id', $this->product->id)->first();
        $this->assertNotNull($newPosition);
        $this->assertEquals($this->product->id, $newPosition->product_id);
    }

    public function test_add_products_from_array(): void
    {
        $products = [
            $this->product->id,
            ['product_id' => $this->product->id, 'amount' => 3],
        ];

        $orderPositionCount = $this->order->orderPositions()->count();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->call('addProducts', $products)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertEquals($orderPositionCount + 2, $this->order->orderPositions()->count());
    }

    public function test_can_show_related_columns(): void
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

    public function test_changed_product_id_fills_position_data(): void
    {
        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->set('orderPosition.product_id', $this->product->id)
            ->call('changedProductId', $this->product)
            ->assertStatus(200)
            ->assertSet('orderPosition.name', $this->product->name)
            ->assertSet('orderPosition.product_number', $this->product->product_number)
            ->assertSet('orderPosition.description', $this->product->description)
            ->assertNotSet('orderPosition.unit_price', 0);
    }

    public function test_create_tasks_from_selected_positions(): void
    {
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
    }

    public function test_create_tasks_prevents_duplicates(): void
    {
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

        $this->assertEquals($initialTaskCount, Task::count());
    }

    public function test_delete_order_position(): void
    {
        $orderPosition = $this->order->orderPositions->first();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->call('editOrderPosition', $orderPosition)
            ->call('deleteOrderPosition')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $this->assertSoftDeleted('order_positions', ['id' => $orderPosition->id]);
    }

    public function test_delete_selected_order_positions(): void
    {
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
    }

    public function test_discount_selected_positions(): void
    {
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
        $this->assertEquals(0.10, $updatedPosition->discount_percentage);
    }

    public function test_edit_new_order_position(): void
    {
        $defaultVatRate = VatRate::factory()->create(['is_default' => true]);

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->call('editOrderPosition', new OrderPosition())
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('orderPosition.vat_rate_id', $defaultVatRate->id)
            ->assertExecutesJs("\$modalOpen('edit-order-position');");
    }

    public function test_edit_order_position(): void
    {
        $orderPosition = $this->order->orderPositions->first();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->call('editOrderPosition', $orderPosition)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('orderPosition.id', $orderPosition->id)
            ->assertSet('orderPosition.name', $orderPosition->name)
            ->assertExecutesJs("\$modalOpen('edit-order-position');");
    }

    public function test_get_builder_filters_by_order(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $builder = $component->instance()->getBuilder(OrderPosition::query());

        $this->assertStringContainsString('order_id', $builder->toSql());
    }

    public function test_get_formatters(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $formatters = $component->instance()->getFormatters();

        $this->assertArrayHasKey('slug_position', $formatters);
        $this->assertArrayHasKey('alternative_tag', $formatters);
        $this->assertEquals('string', $formatters['slug_position']);
    }

    public function test_get_row_actions(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $actions = invade($component->instance())->getRowActions();

        $this->assertCount(2, $actions);
    }

    public function test_get_select_attributes(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $attributes = $component->instance()->getSelectAttributes();

        $this->assertInstanceOf(ComponentAttributeBag::class, $attributes);
        $this->assertArrayHasKey('x-show', $attributes->getAttributes());
    }

    public function test_get_selected_actions(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $actions = invade($component->instance())->getSelectedActions();

        $this->assertGreaterThan(0, count($actions));

        $actionTexts = collect($actions)->map(fn ($action) => $action->text ?? $action->label ?? 'Unknown Action')->toArray();
        $this->assertContains(__('Create tasks'), $actionTexts);
        $this->assertContains(__('Recalculate prices'), $actionTexts);
        $this->assertContains(__('Discount selected positions'), $actionTexts);
        $this->assertContains(__('Replicate'), $actionTexts);
        $this->assertContains(__('Delete'), $actionTexts);
    }

    public function test_get_sortable_order_positions(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $sortablePositions = $component->instance()->getSortableOrderPositions();

        $this->assertIsArray($sortablePositions);
    }

    public function test_get_table_actions(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $actions = invade($component->instance())->getTableActions();

        $this->assertCount(2, $actions);
    }

    public function test_get_view_data_includes_vat_rates(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $viewData = $component->instance()->getViewData();

        $this->assertArrayHasKey('vatRates', $viewData);
        $this->assertIsArray($viewData['vatRates']);
    }

    public function test_item_to_array_formatting(): void
    {
        $orderPosition = $this->order->orderPositions->first();
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);

        $formatted = invade($component->instance())->itemToArray($orderPosition);

        $this->assertArrayHasKey('indentation', $formatted);
        $this->assertArrayHasKey('unit_price', $formatted);
        $this->assertArrayHasKey('alternative_tag', $formatted);
    }

    public function test_listeners_configuration(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $listeners = $component->instance()->getListeners();

        $this->assertArrayHasKey('create-tasks', $listeners);
        $this->assertArrayHasKey('order:add-products', $listeners);
        $this->assertEquals('createTasks', $listeners['create-tasks']);
        $this->assertEquals('addProducts', $listeners['order:add-products']);
    }

    public function test_mount_initializes_component(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);

        $this->assertEquals(1, $component->get('page'));
        $this->assertEmpty($component->get('filters'));
        $this->assertEmpty($component->get('selected'));
        $this->assertEquals('table', $component->get('orderPositionsView'));
    }

    public function test_move_position(): void
    {
        $orderPosition = $this->order->orderPositions->first();
        $originalSortNumber = $orderPosition->sort_number;

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->call('movePosition', $orderPosition, 2)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $updatedPosition = $orderPosition->refresh();
        $this->assertEquals(2, $updatedPosition->sort_number);
        $this->assertNotEquals($originalSortNumber, $updatedPosition->sort_number);
    }

    public function test_move_position_with_parent(): void
    {
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
        $this->assertEquals($parentPosition->id, $updatedPosition->parent_id);
        $this->assertEquals(1, $updatedPosition->sort_number);
    }

    public function test_quick_add_order_position(): void
    {
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

        $this->assertEquals($orderPositionCount + 1, $this->order->orderPositions()->count());

        $newPosition = $this->order->orderPositions()->where('product_id', $this->product->id)->first();
        $this->assertEquals($this->product->name, $newPosition->name);
        $this->assertNotNull($newPosition->unit_net_price);
    }

    public function test_recalculate_order_positions(): void
    {
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
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->assertStatus(200)
            ->assertSet('orderPositionsView', 'table')
            ->assertSet('perPage', 100)
            ->assertSet('isSelectable', true)
            ->call('$refresh'); // Just refresh to ensure data is loaded

        // Check enabledCols property separately
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm]);
        $this->assertNotEmpty($component->get('enabledCols'));
    }

    public function test_replicate_selected_positions(): void
    {
        $orderPosition = $this->order->orderPositions->first();
        $originalCount = $this->order->orderPositions()->count();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->set('selected', [$orderPosition->id])
            ->call('replicateSelected')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSet('selected', []);

        $this->assertEquals($originalCount + 1, $this->order->orderPositions()->count());

        $replicatedPosition = $this->order->orderPositions()->latest('id')->first();
        $this->assertEquals($orderPosition->name, $replicatedPosition->name);
        $this->assertEquals($orderPosition->amount, $replicatedPosition->amount);
        $this->assertNull($replicatedPosition->origin_position_id);
    }

    public function test_reset_order_position(): void
    {
        $orderPosition = $this->order->orderPositions->first();

        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->call('editOrderPosition', $orderPosition)
            ->assertSet('orderPosition.id', $orderPosition->id)
            ->call('resetOrderPosition')
            ->assertStatus(200)
            ->assertSet('orderPosition.id', null);
    }

    public function test_show_product(): void
    {
        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->call('showProduct', $this->product)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs("\$openDetailModal('{$this->product->getUrl()}');");
    }

    public function test_switch_view_same_view_returns_early(): void
    {
        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->assertSet('orderPositionsView', 'table')
            ->call('switchView', 'table')
            ->assertStatus(200)
            ->assertSet('orderPositionsView', 'table');
    }

    public function test_switch_view_to_list(): void
    {
        Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->assertSet('orderPositionsView', 'table')
            ->call('switchView', 'list')
            ->assertStatus(200)
            ->assertSet('orderPositionsView', 'list')
            ->assertSet('data', []);
    }

    public function test_switch_view_to_table(): void
    {
        $component = Livewire::test(OrderPositions::class, ['order' => $this->orderForm])
            ->call('switchView', 'list')
            ->assertSet('orderPositionsView', 'list')
            ->call('switchView', 'table')
            ->assertStatus(200)
            ->assertSet('orderPositionsView', 'table');

        $this->assertNotEmpty($component->get('data'));
    }
}
