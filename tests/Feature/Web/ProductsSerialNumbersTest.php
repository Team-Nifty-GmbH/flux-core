<?php

use FluxErp\Models\Permission;
use FluxErp\Models\SerialNumber;

beforeEach(function (): void {
    $this->serialNumber = SerialNumber::factory()->create();
});

test('products id serial numbers no user', function (): void {
    $this->actingAsGuest();

    $this->get('/products/serial-numbers/' . $this->serialNumber->id)
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('products id serial numbers page', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
        ->assertOk();
});

test('products id serial numbers serial number not found', function (): void {
    $this->serialNumber->delete();

    $this->user->givePermissionTo(
        Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
        ->assertNotFound();
});

test('products id serial numbers without permission', function (): void {
    Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
        ->assertForbidden();
});

test('products serial numbers no user', function (): void {
    $this->actingAsGuest();

    $this->get('/products/serial-numbers')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('products serial numbers page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('products.serial-numbers.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
        ->assertOk();
});

test('products serial numbers without permission', function (): void {
    Permission::findOrCreate('products.serial-numbers.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
        ->assertForbidden();
});
