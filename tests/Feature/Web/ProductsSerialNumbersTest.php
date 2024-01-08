<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Models\SerialNumber;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductsSerialNumbersTest extends BaseSetup
{
    use DatabaseTransactions;

    private SerialNumber $serialNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serialNumber = SerialNumber::factory()->create();
    }

    public function test_products_serial_numbers_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('products.serial-numbers.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
            ->assertStatus(200);
    }

    public function test_products_serial_numbers_no_user()
    {
        $this->get('/products/serial-numbers')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_serial_numbers_without_permission()
    {
        Permission::findOrCreate('products.serial-numbers.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
            ->assertStatus(403);
    }

    public function test_products_id_serial_numbers_page()
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
            ->assertStatus(200);
    }

    public function test_products_id_serial_numbers_page_without_id()
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers/0')
            ->assertStatus(200);
    }

    public function test_products_id_serial_numbers_no_user()
    {
        $this->get('/products/serial-numbers/' . $this->serialNumber->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_id_serial_numbers_without_permission()
    {
        Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
            ->assertStatus(403);
    }

    public function test_products_id_serial_numbers_serial_number_not_found()
    {
        $this->serialNumber->delete();

        $this->user->givePermissionTo(
            Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
            ->assertStatus(404);
    }
}
