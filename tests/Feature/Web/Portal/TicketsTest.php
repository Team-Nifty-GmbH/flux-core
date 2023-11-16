<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Address;
use FluxErp\Models\Permission;
use FluxErp\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketsTest extends PortalSetup
{
    use DatabaseTransactions;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ticket = Ticket::factory()->create([
            'authenticatable_type' => Address::class,
            'authenticatable_id' => $this->user->id,
        ]);
    }

    public function test_portal_tickets_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('tickets.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.tickets'))
            ->assertStatus(200);
    }

    public function test_portal_tickets_no_user()
    {
        $this->get(route('portal.tickets'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_tickets_without_permission()
    {
        Permission::findOrCreate('tickets.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.tickets'))
            ->assertStatus(403);
    }

    public function test_portal_tickets_id_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
            ->assertStatus(200);
    }

    public function test_portal_tickets_id_no_user()
    {
        $this->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_tickets_id_without_permission()
    {
        Permission::findOrCreate('tickets.{id}.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
            ->assertStatus(403);
    }

    public function test_portal_tickets_id_ticket_not_found()
    {
        $this->ticket->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('tickets.{id}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.tickets.id', ['id' => $this->ticket->id]))
            ->assertStatus(404);
    }
}
