<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ServiceTest extends PortalSetup
{
    use DatabaseTransactions;

    public function test_portal_service_page()
    {
        $product = Product::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $serialNumber = SerialNumber::factory()->create([
            'product_id' => $product->id,
            'address_id' => $this->user->id,
        ]);

        $this->user->givePermissionTo(
            Permission::findOrCreate('service.{serialnumberid?}.get', 'address')
        );

        $this->actingAs($this->user, 'address')->get(
            route('portal.service', ['serialNumberId' => $serialNumber->id])
        )
            ->assertStatus(200);
    }

    public function test_portal_service_page_without_serial_number()
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('service.{serialnumberid?}.get', 'address')
        );

        $this->actingAs($this->user, 'address')->get(route('portal.service', ['serialNumberId' => null]))
            ->assertStatus(200);
    }

    public function test_portal_service_no_user()
    {
        $this->get(route('portal.service', ['serialNumberId' => null]))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain.'/login');
    }

    public function test_portal_service_without_permission()
    {
        Permission::findOrCreate('service.{serialnumberid?}.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.service', ['serialNumberId' => null]))
            ->assertStatus(403);
    }
}
