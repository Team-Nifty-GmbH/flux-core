<?php

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\DeleteProject;
use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;

test('create project', function (): void {
    $project = CreateProject::make([
        'name' => 'Website Redesign',
        'tenant_id' => $this->dbTenant->getKey(),
    ])->validate()->execute();

    expect($project)->toBeInstanceOf(Project::class)
        ->name->toBe('Website Redesign');
});

test('create project requires name', function (): void {
    CreateProject::assertValidationErrors(
        ['tenant_id' => $this->dbTenant->getKey()],
        'name'
    );
});

test('update project', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $updated = UpdateProject::make([
        'id' => $project->getKey(),
        'name' => 'App Rewrite',
    ])->validate()->execute();

    expect($updated->name)->toBe('App Rewrite');
});

test('delete project', function (): void {
    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    expect(DeleteProject::make(['id' => $project->getKey()])
        ->validate()->execute())->toBeTrue();
});

function createOrderForContact(Contact $contact, OrderTypeEnum $orderTypeEnum = OrderTypeEnum::Order): Order
{
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    return Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => test()->defaultLanguage->getKey(),
        'order_type_id' => OrderType::factory()
            ->create([
                'order_type_enum' => $orderTypeEnum,
                'is_active' => true,
            ])
            ->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => test()->dbTenant->getKey(),
    ]);
}

function createProjectForContact(Contact $contact, ?Order $mainOrder = null): Project
{
    return Project::factory()->create([
        'tenant_id' => test()->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'order_id' => $mainOrder?->getKey(),
    ]);
}

test('update project syncs supplementary orders', function (): void {
    $contact = Contact::factory()->hasAttached(factory: $this->dbTenant, relationship: 'tenants')->create();
    $project = createProjectForContact($contact, createOrderForContact($contact));
    $first = createOrderForContact($contact);
    $second = createOrderForContact($contact);

    UpdateProject::make([
        'id' => $project->getKey(),
        'supplementary_orders' => [$first->getKey(), $second->getKey()],
    ])->validate()->execute();

    expect($project->supplementaryOrders()->orderBy('orders.id')->pluck('orders.id')->all())
        ->toBe([$first->getKey(), $second->getKey()]);

    UpdateProject::make([
        'id' => $project->getKey(),
        'supplementary_orders' => [],
    ])->validate()->execute();

    expect($project->supplementaryOrders()->count())->toBe(0);
});

test('update project keeps supplementary orders when the key is missing', function (): void {
    $contact = Contact::factory()->hasAttached(factory: $this->dbTenant, relationship: 'tenants')->create();
    $project = createProjectForContact($contact);
    $order = createOrderForContact($contact);
    $project->supplementaryOrders()->attach($order->getKey());

    UpdateProject::make(['id' => $project->getKey(), 'name' => 'Renamed'])->validate()->execute();

    expect($project->supplementaryOrders()->pluck('orders.id')->all())->toBe([$order->getKey()]);
});

test('the main order cannot be a supplementary order', function (): void {
    $contact = Contact::factory()->hasAttached(factory: $this->dbTenant, relationship: 'tenants')->create();
    $mainOrder = createOrderForContact($contact);
    $project = createProjectForContact($contact, $mainOrder);

    UpdateProject::assertValidationErrors(
        ['id' => $project->getKey(), 'supplementary_orders' => [$mainOrder->getKey()]],
        'supplementary_orders'
    );
});

test('a purchase order cannot be a supplementary order', function (): void {
    $contact = Contact::factory()->hasAttached(factory: $this->dbTenant, relationship: 'tenants')->create();
    $project = createProjectForContact($contact);
    $purchase = createOrderForContact($contact, OrderTypeEnum::Purchase);

    UpdateProject::assertValidationErrors(
        ['id' => $project->getKey(), 'supplementary_orders' => [$purchase->getKey()]],
        'supplementary_orders'
    );
});

test('an order of another contact cannot be a supplementary order', function (): void {
    $contact = Contact::factory()->hasAttached(factory: $this->dbTenant, relationship: 'tenants')->create();
    $otherContact = Contact::factory()->hasAttached(factory: $this->dbTenant, relationship: 'tenants')->create();
    $project = createProjectForContact($contact);

    UpdateProject::assertValidationErrors(
        ['id' => $project->getKey(), 'supplementary_orders' => [createOrderForContact($otherContact)->getKey()]],
        'supplementary_orders'
    );
});

test('the contact check uses the contact from the same update', function (): void {
    $contact = Contact::factory()->hasAttached(factory: $this->dbTenant, relationship: 'tenants')->create();
    $newContact = Contact::factory()->hasAttached(factory: $this->dbTenant, relationship: 'tenants')->create();
    $project = createProjectForContact($contact);
    $order = createOrderForContact($newContact);

    UpdateProject::make([
        'id' => $project->getKey(),
        'contact_id' => $newContact->getKey(),
        'supplementary_orders' => [$order->getKey()],
    ])->validate()->execute();

    expect($project->supplementaryOrders()->pluck('orders.id')->all())->toBe([$order->getKey()]);
});
