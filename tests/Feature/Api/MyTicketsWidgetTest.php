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

test('the my tickets widget api returns open tickets with the authenticatable label', function (): void {
    $createUserTicket = function (array $attributes): Ticket {
        $ticket = Ticket::factory()->create($attributes);
        $ticket->users()->attach($this->user->getKey());

        return $ticket;
    };

    $contact = Contact::factory()->create();
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

    $companyAddressTicket = $createUserTicket([
        'state' => TicketInProgress::class,
        'authenticatable_type' => $addressWithCompany->getMorphClass(),
        'authenticatable_id' => $addressWithCompany->getKey(),
    ]);
    $addressTicket = $createUserTicket([
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
        ->and($data->firstWhere('id', $companyAddressTicket->getKey())['authenticatable'])
        ->toEqual('Own Company AG, Erika Muster')
        ->and($data->firstWhere('id', $addressTicket->getKey())['authenticatable'])->toEqual('Max Muster')
        ->and($data->firstWhere('id', $userTicket->getKey())['authenticatable'])->toEqual($this->user->name)
        ->and($data->first())->toHaveKeys(['id', 'ticket_number', 'title', 'state', 'url', 'authenticatable']);
});

test('the my tickets widget api respects the limit parameter', function (): void {
    Ticket::factory()
        ->count(3)
        ->create([
            'state' => TicketInProgress::class,
            'authenticatable_type' => $this->user->getMorphClass(),
            'authenticatable_id' => $this->user->getKey(),
        ])
        ->each(fn (Ticket $ticket) => $ticket->users()->attach($this->user->getKey()));

    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/widgets/my-tickets?limit=2')->assertOk();

    expect($response->json('data'))->toHaveCount(2);

    $this->getJson('/api/widgets/my-tickets?limit=0')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('limit');
});

test('the my tickets widget api forbids users without the permission', function (): void {
    $otherUser = User::factory()->create([
        'language_id' => Language::factory()->create()->id,
    ]);
    Sanctum::actingAs($otherUser, ['user']);

    $this->getJson('/api/widgets/my-tickets')->assertForbidden();
});
