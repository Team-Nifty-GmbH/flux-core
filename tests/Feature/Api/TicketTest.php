<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class TicketTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $tickets;

    private Address $address;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $dbContact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
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
            'authenticatable_type' => Address::class,
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
    }

    public function test_get_ticket()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/tickets/' . $this->tickets[0]->id);
        $response->assertStatus(200);

        $ticket = json_decode($response->getContent())->data;

        $this->assertEquals($this->tickets[0]->id, $ticket->id);
        $this->assertEquals($this->tickets[0]->authenticatable_type, $ticket->authenticatable_type);
        $this->assertEquals($this->tickets[0]->authenticatable_id, $ticket->authenticatable_id);
        $this->assertEquals($this->tickets[0]->model_type, $ticket->model_type);
        $this->assertEquals($this->tickets[0]->model_id, $ticket->model_id);
        $this->assertEquals($this->tickets[0]->ticket_type_id, $ticket->ticket_type_id);
        $this->assertEquals($this->tickets[0]->title, $ticket->title);
        $this->assertEquals($this->tickets[0]->description, $ticket->description);
    }

    public function test_get_tickets()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/tickets');
        $response->assertStatus(200);

        $tickets = json_decode($response->getContent())->data->data;

        $this->assertEquals($this->tickets->count(), count($tickets));
        //Todo Assert created Ticket instances
    }

    public function test_create_ticket()
    {
        $ticket = [
            'authenticatable_id' => $this->address->id,
            'authenticatable_type' => Address::class,
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

        $this->assertEquals($ticket['authenticatable_type'], $dbTicket->authenticatable_type);
        $this->assertEquals($ticket['authenticatable_id'], $dbTicket->authenticatable_id);
        $this->assertEquals($ticket['title'], $dbTicket->title);
        $this->assertEquals($ticket['description'], $dbTicket->description);
    }

    public function test_create_ticket_validation_fails()
    {
        $ticket = [
            'title' => Str::random(),
            'description' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/tickets', $ticket);
        $response->assertStatus(422);
    }

    public function test_update_ticket()
    {
        $ticket = [
            'id' => $this->tickets[1]->id,
            'authenticatable_type' => Address::class,
            'authenticatable_id' => $this->address->id,
            'title' => 'Title Update Test',
            'description' => 'Description Update Test',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/tickets', $ticket);
        $response->assertStatus(200);

        $responseTicket = json_decode($response->getContent())->data;

        $dbTicket = Ticket::query()
            ->whereKey($responseTicket->id)
            ->first();

        $this->assertEquals($ticket['id'], $dbTicket->id);
        $this->assertEquals($ticket['authenticatable_type'], $dbTicket->authenticatable_type);
        $this->assertEquals($ticket['authenticatable_id'], $dbTicket->authenticatable_id);
        $this->assertEquals($ticket['title'], $dbTicket->title);
        $this->assertEquals($ticket['description'], $dbTicket->description);
    }

    public function test_update_ticket_with_additional_columns()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => Ticket::class,
        ]);

        $this->tickets[0]->saveMeta($additionalColumn->name, 'Original Value');

        $ticket = [
            'id' => $this->tickets[0]->id,
            'authenticatable_id' => $this->address->id,
            'authenticatable_type' => Address::class,
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

        $this->assertEquals($ticket['id'], $dbTicket->id);
        $this->assertEquals($ticket['authenticatable_id'], $dbTicket->authenticatable_id);
        $this->assertEquals($ticket['authenticatable_type'], $dbTicket->authenticatable_type);
        $this->assertEquals($ticket['state'], $dbTicket->state);
        $this->assertEquals($ticket['title'], $dbTicket->title);
        $this->assertEquals($ticket['description'], $dbTicket->description);
    }

    public function test_update_ticket_validation_fails()
    {
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
    }

    public function test_delete_ticket()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/tickets/' . $this->tickets[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(Ticket::query()->whereKey($this->tickets[0]->id)->exists());
    }

    public function test_delete_ticket_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/tickets/' . ++$this->tickets[4]->id);
        $response->assertStatus(404);
    }

    public function test_attach_user_assignment()
    {
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

        $this->assertTrue($ticket->users()->where('users.id', $this->user->id)->exists());
    }

    public function test_attach_user_assignment_validation_fails()
    {
        $ticket = [
            'user_id' => $this->user->id,
        ];

        $this->user->givePermissionTo($this->permissions['toggle']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/tickets/toggle', $ticket);
        $response->assertStatus(422);
    }

    public function test_detach_user_assignment()
    {
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

        $this->assertFalse($ticket->users()->where('users.id', $this->user->id)->exists());
    }
}
