<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;

test('portal service no user', function (): void {
    $this->get(route('portal.service', ['serialNumberId' => null]))
        ->assertStatus(302)
        ->assertRedirect($this->portalDomain . '/login');
});

test('portal service page', function (): void {
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
});

test('portal service page without serial number', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('service.{serialnumberid?}.get', 'address')
    );

    $this->actingAs($this->user, 'address')->get(route('portal.service', ['serialNumberId' => null]))
        ->assertStatus(200);
});

test('portal service without permission', function (): void {
    Permission::findOrCreate('service.{serialnumberid?}.get', 'address');

    $this->actingAs($this->user, 'address')->get(route('portal.service', ['serialNumberId' => null]))
        ->assertStatus(403);
});
