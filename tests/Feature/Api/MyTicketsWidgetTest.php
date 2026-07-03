<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\States\Ticket\Closed as TicketClosed;
use FluxErp\States\Ticket\Escalated as TicketEscalated;
use FluxErp\States\Ticket\InProgress as TicketInProgress;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->permission = Permission::findOrCreate('api.widgets.my-tickets.get', 'sanctum');
});

test('the my tickets widget api returns open tickets with a creator company', function (): void {
    $createUserTicket = function (array $attributes): Ticket {
        $ticket = Ticket::factory()->create($attributes);
        $ticket->users()->attach($this->user->getKey());

        return $ticket;
    };

    $contact = Contact::factory()->create();
    $mainAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => 'Main Address GmbH',
        'is_main_address' => true,
    ]);
    $addressWithCompany = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => 'Own Company AG',
        'firstname' => 'Erika',
        'lastname' => 'Muster',
        'is_main_address' => false,
    ]);
    $addressWithoutCompany = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => null,
        'firstname' => 'Max',
        'lastname' => 'Muster',
        'is_main_address' => false,
    ]);

    $ownCompanyTicket = $createUserTicket([
        'state' => TicketInProgress::class,
        'authenticatable_type' => $addressWithCompany->getMorphClass(),
        'authenticatable_id' => $addressWithCompany->getKey(),
    ]);
    $mainAddressCompanyTicket = $createUserTicket([
        'state' => TicketInProgress::class,
        'authenticatable_type' => $addressWithoutCompany->getMorphClass(),
        'authenticatable_id' => $addressWithoutCompany->getKey(),
    ]);
    $userTicket = $createUserTicket([
        'state' => TicketEscalated::class,
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
    ]);
    $closedTicket = $createUserTicket([
        'state' => TicketClosed::class,
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
    ]);

    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/widgets/my-tickets')->assertOk();

    $data = collect($response->json('data'));
    expect($data->pluck('id'))->not->toContain($closedTicket->getKey())
        ->and($data->first()['id'])->toEqual($userTicket->getKey())
        ->and($data->firstWhere('id', $ownCompanyTicket->getKey())['creator'])->toEqual('Own Company AG')
        ->and($data->firstWhere('id', $mainAddressCompanyTicket->getKey())['creator'])->toEqual('Main Address GmbH')
        ->and($data->firstWhere('id', $userTicket->getKey())['creator'])->toEqual($this->user->name)
        ->and($data->first())->toHaveKeys(['id', 'ticket_number', 'title', 'state', 'url', 'creator']);
});

test('the my tickets widget api falls back to the main address company when the creator company is an empty string', function (): void {
    $createUserTicket = function (array $attributes): Ticket {
        $ticket = Ticket::factory()->create($attributes);
        $ticket->users()->attach($this->user->getKey());

        return $ticket;
    };

    $contact = Contact::factory()->create();
    $mainAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => 'Main Address GmbH',
        'is_main_address' => true,
    ]);
    $addressWithEmptyCompany = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => '',
        'firstname' => 'Erika',
        'lastname' => 'Muster',
        'is_main_address' => false,
    ]);

    $ticket = $createUserTicket([
        'state' => TicketInProgress::class,
        'authenticatable_type' => $addressWithEmptyCompany->getMorphClass(),
        'authenticatable_id' => $addressWithEmptyCompany->getKey(),
    ]);

    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/widgets/my-tickets')->assertOk();

    $data = collect($response->json('data'));
    expect($data->firstWhere('id', $ticket->getKey())['creator'])->toEqual('Main Address GmbH');
});

test('the my tickets widget api forbids users without the permission', function (): void {
    $otherUser = User::factory()->create([
        'language_id' => Language::factory()->create()->id,
    ]);
    Sanctum::actingAs($otherUser, ['user']);

    $this->getJson('/api/widgets/my-tickets')->assertForbidden();
});
