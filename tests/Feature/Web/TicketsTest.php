<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_tickets_page()
    {
        $this->user->givePermissionTo(Permission::findByName('tickets.get', 'web'));

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
        $id = 1;

        $this->user->givePermissionTo(Permission::findByName('tickets.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/tickets/$id')
            ->assertStatus(200);
    }

    public function test_tickets_id_no_user()
    {
        $id = 1;

        $this->get('/tickets/$id')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_tickets_id_without_permission()
    {
        $id = 1;

        $this->actingAs($this->user, 'web')->get('/tickets/$id')
            ->assertStatus(403);
    }
}
