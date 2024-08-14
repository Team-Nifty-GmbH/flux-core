<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductTest extends PortalSetup
{
    use DatabaseTransactions;

    private SerialNumber $serialNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $product = Product::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $this->serialNumber = SerialNumber::factory()->create([
            'product_id' => $product->id,
            'address_id' => $this->user->id,
        ]);
    }

    public function test_portal_product_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('product.{id}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.product', ['id' => $this->serialNumber->id]))
            ->assertStatus(200);
    }

    public function test_portal_product_no_user()
    {
        $this->get(route('portal.product', ['id' => $this->serialNumber->id]))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain.'/login');
    }

    public function test_portal_product_without_permission()
    {
        Permission::findOrCreate('product.{id}.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.product', ['id' => $this->serialNumber->id]))
            ->assertStatus(403);
    }

    public function test_portal_product_serial_number_not_found()
    {
        $this->serialNumber->update(['address_id' => null]);

        $this->user->givePermissionTo(Permission::findOrCreate('product.{id}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.product', ['id' => $this->serialNumber->id]))
            ->assertStatus(404);
    }
}
