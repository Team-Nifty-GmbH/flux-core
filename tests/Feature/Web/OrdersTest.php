<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrdersTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_orders_page()
    {
        $this->user->givePermissionTo(Permission::findByName('orders.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/orders')
            ->assertStatus(200);
    }

    public function test_orders_no_user()
    {
        $this->get('/orders')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_orders_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/orders')
            ->assertStatus(403);
    }
}
