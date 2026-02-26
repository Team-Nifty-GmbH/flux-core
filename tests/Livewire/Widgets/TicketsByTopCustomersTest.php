<?php

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\TicketsByTopCustomers;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketsByTopCustomers::class)
        ->assertOk();
});

test('shows top customers by ticket count with type breakdown', function (): void {
    $contact1 = Contact::factory()
        ->has(Address::factory()->state(['tenant_id' => $this->dbTenant->getKey()]))
        ->create();
    $contact2 = Contact::factory()
        ->has(Address::factory()->state(['tenant_id' => $this->dbTenant->getKey()]))
        ->create();

    $address1 = $contact1->addresses()->first();
    $address2 = $contact2->addresses()->first();
    $ticketType = TicketType::factory()->create();

    Ticket::factory()->count(3)->create([
        'authenticatable_type' => $address1->getMorphClass(),
        'authenticatable_id' => $address1->getKey(),
        'ticket_type_id' => $ticketType->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    Ticket::factory()->create([
        'authenticatable_type' => $address2->getMorphClass(),
        'authenticatable_id' => $address2->getKey(),
        'ticket_type_id' => $ticketType->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    $component = Livewire::test(TicketsByTopCustomers::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $component->assertOk();

    $labels = $component->get('labels');
    $series = $component->get('series');
    $typeSeries = collect($series)->firstWhere('name', $ticketType->name);

    expect($labels)->toHaveCount(2)
        ->and($typeSeries)->not->toBeNull()
        ->and($typeSeries['data'][0])->toBe(3);
});

test('respects time frame', function (): void {
    $contact = Contact::factory()
        ->has(Address::factory()->state(['tenant_id' => $this->dbTenant->getKey()]))
        ->create();

    $address = $contact->addresses()->first();

    Ticket::factory()->create([
        'authenticatable_type' => $address->getMorphClass(),
        'authenticatable_id' => $address->getKey(),
        'state' => WaitingForSupport::class,
        'created_at' => now()->subYear(),
    ]);

    $component = Livewire::test(TicketsByTopCustomers::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $series = $component->get('series');

    expect($series)->toBeEmpty();
});
