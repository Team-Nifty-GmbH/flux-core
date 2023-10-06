<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketsTest extends BaseSetup
{
    use DatabaseTransactions;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $dbContact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $address = Address::factory()->create([
            'client_id' => $dbContact->client_id,
            'language_id' => $this->user->language_id,
            'contact_id' => $dbContact->id,
            'is_main_address' => true,
        ]);

        $this->ticket = Ticket::factory()->create([
            'authenticatable_type' => Address::class,
            'authenticatable_id' => $address->id,
        ]);
    }

    public function test_tickets_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('tickets.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/tickets')
            ->assertStatus(200);
    }

    public function test_tickets_no_user()
    {
        $this->get('/tickets')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_tickets_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/tickets')
            ->assertStatus(403);
    }

    public function test_tickets_id_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/tickets/' . $this->ticket->id)
            ->assertStatus(200);
    }

    public function test_tickets_id_no_user()
    {
        $this->get('/tickets/' . $this->ticket->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_tickets_id_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/tickets/' . $this->ticket->id)
            ->assertStatus(403);
    }

    public function test_tickets_id_ticket_not_found()
    {
        $this->ticket->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/tickets/' . $this->ticket->id)
            ->assertStatus(404);
    }
}
