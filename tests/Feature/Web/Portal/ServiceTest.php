<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;

class ServiceTest extends PortalSetup
{
    public function test_portal_service_no_user(): void
    {
        $this->get(route('portal.service', ['serialNumberId' => null]))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_service_page(): void
    {
        $product = Product::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $warehouse = Warehouse::factory()->create();
        $serialNumber = SerialNumber::factory()->create();
        StockPosting::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'serial_number_id' => $serialNumber->id,
            'posting' => 1,
        ]);

        $serialNumber->addresses()->attach($this->user->id);

        $this->user->givePermissionTo(
            Permission::findOrCreate('service.{serialnumberid?}.get', 'address')
        );

        $this->actingAs($this->user, 'address')->get(
            route('portal.service', ['serialNumberId' => $serialNumber->id])
        )
            ->assertStatus(200);
    }

    public function test_portal_service_page_without_serial_number(): void
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('service.{serialnumberid?}.get', 'address')
        );

        $this->actingAs($this->user, 'address')->get(route('portal.service', ['serialNumberId' => null]))
            ->assertStatus(200);
    }

    public function test_portal_service_without_permission(): void
    {
        Permission::findOrCreate('service.{serialnumberid?}.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.service', ['serialNumberId' => null]))
            ->assertStatus(403);
    }
}
