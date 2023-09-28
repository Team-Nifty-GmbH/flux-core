<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductsSerialNumbersTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_products_serial_numbers_page()
    {
        $this->user->givePermissionTo(Permission::findByName('products.serial-numbers.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
            ->assertStatus(200);
    }

    public function test_products_serial_numbers_no_user()
    {
        $this->get('/products/serial_numbers')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_serial_numbers_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
            ->assertStatus(403);
    }
}
