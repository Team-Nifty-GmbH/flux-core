<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\OrderProject;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function (): void {
    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
    $priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create([
            'is_default' => true,
        ]);

    $orderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Order->value,
    ]);

    $address = Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $contact->id,
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);

    $this->order = Order::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'currency_id' => $currency->id,
        'address_invoice_id' => $address->id,
        'price_list_id' => $priceList->id,
        'payment_type_id' => $paymentType->id,
        'order_type_id' => $orderType->id,
    ]);
});

test('can create project from order', function (): void {
    $projectName = Str::uuid();

    /** @var Testable $component */
    $component = Livewire::withoutLazyLoading()
        ->test(OrderProject::class, ['order' => $this->order])
        ->set('existingProject', false)
        ->assertSet('form.id', null)
        ->assertSet('form.order_id', $this->order->id)
        ->assertSet('form.client_id', $this->order->client_id)
        ->assertSet('form.contact_id', $this->order->contact_id)
        ->assertSet('form.start_date', $this->order->system_delivery_date)
        ->assertSet('form.end_date', $this->order->system_delivery_date_end)
        ->assertSet('form.name', $this->order->getLabel())
        ->set('form.name', $projectName)
        ->call('save');

    $component
        ->assertDispatchedTo('order.order-positions', 'create-tasks', $component->get('form.id'))
        ->assertReturned(true)
        ->assertStatus(200)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('projects', [
        'id' => $component->get('form.id'),
        'order_id' => $this->order->id,
        'client_id' => $this->order->client_id,
        'contact_id' => $this->order->contact_id,
        'start_date' => $this->order->system_delivery_date,
        'end_date' => $this->order->system_delivery_date_end,
        'name' => $projectName,
    ]);
});

test('create tasks for existing project', function (): void {
    $projects = Project::factory(3)->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
    $currentProjectCount = Project::query()->count();

    $component = Livewire::withoutLazyLoading()
        ->test(OrderProject::class, ['order' => $this->order])
        ->set('existingProject', true)
        ->assertSet('projectId', null)
        ->assertSet('form.order_id', $this->order->id)
        ->assertSet('form.client_id', $this->order->client_id)
        ->assertSet('form.contact_id', $this->order->contact_id)
        ->assertSet('form.start_date', $this->order->system_delivery_date)
        ->assertSet('form.end_date', $this->order->system_delivery_date_end)
        ->assertSet('form.name', $this->order->getLabel())
        ->call('save')
        ->assertReturned(false)
        ->assertStatus(200)
        ->assertToastNotification(type: 'error')
        ->assertHasErrors(['projectId']);

    $component
        ->set('projectId', $projects->first()->id)
        ->call('save')
        ->assertReturned(true)
        ->assertStatus(200)
        ->assertDispatchedTo('order.order-positions', 'create-tasks', $component->get('form.id'));

    expect(Project::query()->count())->toEqual($currentProjectCount);
});

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(OrderProject::class, ['order' => $this->order])
        ->assertStatus(200)
        ->assertSet('form.order_id', $this->order->id)
        ->assertSet('form.client_id', $this->order->client_id)
        ->assertSet('form.contact_id', $this->order->contact_id)
        ->assertSet('form.start_date', $this->order->system_delivery_date)
        ->assertSet('form.end_date', $this->order->system_delivery_date_end)
        ->assertSet('form.name', $this->order->getLabel());
});
