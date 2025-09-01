<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Notifications\Ticket\TicketAssignedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbContact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $language = Language::query()->where('language_code', config('app.locale'))->first();
    if (! $language) {
        $language = Language::factory()->create(['language_code' => config('app.locale')]);
    }

    $this->address = Address::factory()->create([
        'is_main_address' => true,
        'client_id' => $dbContact->client_id,
        'contact_id' => $dbContact->id,
        'language_id' => $language->id,
    ]);

    $this->tickets = Ticket::factory()->count(5)->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $this->address->id,
    ]);

    $this->tickets[1]->users()->attach($this->user->id);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.tickets.{id}.get'),
        'index' => Permission::findOrCreate('api.tickets.get'),
        'create' => Permission::findOrCreate('api.tickets.post'),
        'update' => Permission::findOrCreate('api.tickets.put'),
        'delete' => Permission::findOrCreate('api.tickets.{id}.delete'),
        'toggle' => Permission::findOrCreate('api.tickets.toggle.post'),
    ];
});

test('attach user assignment', function (): void {
    $this->user->givePermissionTo($this->permissions['toggle']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tickets/toggle', [
        'ticket_id' => $this->tickets[2]->id,
        'user_id' => $this->user->id,
    ]);
    $response->assertStatus(200);

    $ticket = Ticket::query()
        ->whereKey($this->tickets[2]->id)
        ->first();

    expect($ticket->users()->where('users.id', $this->user->id)->exists())->toBeTrue();
});

test('attach user assignment validation fails', function (): void {
    $ticket = [
        'user_id' => $this->user->id,
    ];

    $this->user->givePermissionTo($this->permissions['toggle']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tickets/toggle', $ticket);
    $response->assertStatus(422);
});

test('create ticket', function (): void {
    $ticket = [
        'authenticatable_id' => $this->address->id,
        'authenticatable_type' => morph_alias(Address::class),
        'title' => 'Title Test',
        'description' => 'Description Test',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tickets', $ticket);
    $response->assertStatus(201);

    $responseTicket = json_decode($response->getContent())->data;

    $dbTicket = Ticket::query()
        ->whereKey($responseTicket->id)
        ->first();

    expect($dbTicket->authenticatable_type)->toEqual($ticket['authenticatable_type']);
    expect($dbTicket->authenticatable_id)->toEqual($ticket['authenticatable_id']);
    expect($dbTicket->title)->toEqual($ticket['title']);
    expect($dbTicket->description)->toEqual($ticket['description']);
});

test('create ticket validation fails', function (): void {
    $ticket = [
        'title' => Str::random(),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tickets', $ticket);
    $response->assertStatus(422);
});

test('delete ticket', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/tickets/' . $this->tickets[0]->id);
    $response->assertStatus(204);

    expect(Ticket::query()->whereKey($this->tickets[0]->id)->exists())->toBeFalse();
});

test('delete ticket not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/tickets/' . ++$this->tickets[4]->id);
    $response->assertStatus(404);
});

test('detach user assignment', function (): void {
    $this->user->givePermissionTo($this->permissions['toggle']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/tickets/toggle', [
        'ticket_id' => $this->tickets[1]->id,
        'user_id' => $this->user->id,
    ]);
    $response->assertStatus(200);

    $ticket = Ticket::query()
        ->whereKey($this->tickets[2]->id)
        ->first();

    expect($ticket->users()->where('users.id', $this->user->id)->exists())->toBeFalse();
});

test('get ticket', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/tickets/' . $this->tickets[0]->id);
    $response->assertStatus(200);

    $ticket = json_decode($response->getContent())->data;

    expect($ticket->id)->toEqual($this->tickets[0]->id);
    expect($ticket->authenticatable_type)->toEqual($this->tickets[0]->authenticatable_type);
    expect($ticket->authenticatable_id)->toEqual($this->tickets[0]->authenticatable_id);
    expect($ticket->model_type)->toEqual($this->tickets[0]->model_type);
    expect($ticket->model_id)->toEqual($this->tickets[0]->model_id);
    expect($ticket->ticket_type_id)->toEqual($this->tickets[0]->ticket_type_id);
    expect($ticket->title)->toEqual($this->tickets[0]->title);
    expect($ticket->description)->toEqual($this->tickets[0]->description);
});

test('get tickets', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/tickets');
    $response->assertStatus(200);

    $tickets = json_decode($response->getContent())->data->data;

    expect(count($tickets))->toEqual($this->tickets->count());
    // Todo Assert created Ticket instances
});

test('update ticket', function (): void {
    Notification::fake();
    config(['queue.default' => 'sync']);

    $users = User::factory()->count(3)->create([
        'language_id' => $this->user->language_id,
        'is_active' => true,
    ]);
    $this->tickets[2]->users()->attach($users->last()->id);

    $ticket = [
        'id' => $this->tickets[2]->id,
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $this->address->id,
        'title' => 'Title Update Test',
        'description' => 'Description Update Test',
        'users' => $users->take(2)->pluck('id')->toArray(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tickets', $ticket);
    $response->assertStatus(200);

    $responseTicket = json_decode($response->getContent())->data;

    $dbTicket = Ticket::query()
        ->whereKey($responseTicket->id)
        ->first();

    expect($dbTicket->id)->toEqual($ticket['id']);
    expect($dbTicket->authenticatable_type)->toEqual($ticket['authenticatable_type']);
    expect($dbTicket->authenticatable_id)->toEqual($ticket['authenticatable_id']);
    expect($dbTicket->title)->toEqual($ticket['title']);
    expect($dbTicket->description)->toEqual($ticket['description']);
    expect($dbTicket->users()->pluck('users.id')->toArray())->toEqual($ticket['users']);

    Notification::assertSentTo($users->take(2), TicketAssignedNotification::class);
    Notification::assertNothingSentTo($this->user);
    Notification::assertNotSentTo($users->last(), TicketAssignedNotification::class);
});

test('update ticket validation fails', function (): void {
    $ticket = [
        'address_id' => $this->address->id,
        'state' => 'waiting_for_customer',
        'title' => Str::random(),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tickets', $ticket);
    $response->assertStatus(422);
});

test('update ticket with additional columns', function (): void {
    $additionalColumn = AdditionalColumn::factory()->create([
        'model_type' => Ticket::class,
    ]);

    $this->tickets[0]->saveMeta($additionalColumn->name, 'Original Value');

    $ticket = [
        'id' => $this->tickets[0]->id,
        'authenticatable_id' => $this->address->id,
        'authenticatable_type' => morph_alias(Address::class),
        'state' => 'waiting_for_customer',
        'title' => Str::random(),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/tickets', $ticket);
    $response->assertStatus(200);

    $responseTicket = json_decode($response->getContent())->data;

    $dbTicket = Ticket::query()
        ->whereKey($responseTicket->id)
        ->first();

    expect($dbTicket->id)->toEqual($ticket['id']);
    expect($dbTicket->authenticatable_id)->toEqual($ticket['authenticatable_id']);
    expect($dbTicket->authenticatable_type)->toEqual($ticket['authenticatable_type']);
    expect($dbTicket->state)->toEqual($ticket['state']);
    expect($dbTicket->title)->toEqual($ticket['title']);
    expect($dbTicket->description)->toEqual($ticket['description']);
});
