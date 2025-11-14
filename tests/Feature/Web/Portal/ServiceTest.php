<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;

test('portal service no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.service', ['serialNumberId' => null]))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal service page', function (): void {
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $warehouse = Warehouse::factory()->create();
    $serialNumber = SerialNumber::factory()->create();
    StockPosting::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'serial_number_id' => $serialNumber->id,
        'posting' => 1,
    ]);

    $serialNumber->addresses()->attach($this->address->id);

    $this->address->givePermissionTo(
        Permission::findOrCreate('service.{serialnumberid?}.get', 'address')
    );

    $this->actingAs($this->address, 'address')->get(
        route('portal.service', ['serialNumberId' => $serialNumber->id])
    )
        ->assertOk();
});

test('portal service page without serial number', function (): void {
    $this->address->givePermissionTo(
        Permission::findOrCreate('service.{serialnumberid?}.get', 'address')
    );

    $this->actingAs($this->address, 'address')->get(route('portal.service', ['serialNumberId' => null]))
        ->assertOk();
});

test('portal service without permission', function (): void {
    Permission::findOrCreate('service.{serialnumberid?}.get', 'address');

    $this->actingAs($this->address, 'address')->get(route('portal.service', ['serialNumberId' => null]))
        ->assertForbidden();
});
