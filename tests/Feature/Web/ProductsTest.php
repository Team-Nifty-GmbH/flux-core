<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_products_no_user()
    {
        $this->get('/products')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_redirect_dashboard()
    {
        $this->user->givePermissionTo(Permission::findByName('products.get', 'web'));

        $this->actingAs($this->user, guard: 'web')->get('/products')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));
    }
}
