<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;
use FluxErp\Models\SerialNumber;

beforeEach(function (): void {
    $this->serialNumber = SerialNumber::factory()->create();
});

test('products id serial numbers no user', function (): void {
    $this->get('/products/serial-numbers/' . $this->serialNumber->id)
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('products id serial numbers page', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
        ->assertStatus(200);
});

test('products id serial numbers serial number not found', function (): void {
    $this->serialNumber->delete();

    $this->user->givePermissionTo(
        Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
        ->assertStatus(404);
});

test('products id serial numbers without permission', function (): void {
    Permission::findOrCreate('products.serial-numbers.{id?}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers/' . $this->serialNumber->id)
        ->assertStatus(403);
});

test('products serial numbers no user', function (): void {
    $this->get('/products/serial-numbers')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('products serial numbers page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('products.serial-numbers.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
        ->assertStatus(200);
});

test('products serial numbers without permission', function (): void {
    Permission::findOrCreate('products.serial-numbers.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/serial-numbers')
        ->assertStatus(403);
});
