<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductsListTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_products_list_page()
    {
        $this->user->givePermissionTo(Permission::findByName('products.list.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/list')
            ->assertStatus(200);
    }

    public function test_products_list_no_user()
    {
        $this->get('/products/list')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_list_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/products/list')
            ->assertStatus(403);
    }
}
