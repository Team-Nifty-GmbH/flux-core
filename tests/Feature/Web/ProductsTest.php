<?php

use FluxErp\Models\Category;
use FluxErp\Models\Currency;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;

beforeEach(function (): void {
    $this->product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $category = Category::factory()->create([
        'model_type' => Product::class,
    ]);

    Currency::factory()->create(['is_default' => true]);
    PriceList::factory()->create(['is_default' => true]);

    $this->product->categories()->attach($category->id);
});

test('products id no user', function (): void {
    $this->actingAsGuest();

    $this->get('/products/' . $this->product->id)
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('products id page', function (): void {
    Currency::factory()->create(['is_default' => true]);

    $this->user->givePermissionTo(Permission::findOrCreate('products.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
        ->assertOk();
});

test('products id product not found', function (): void {
    $this->product->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('products.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
        ->assertNotFound();
});

test('products id without permission', function (): void {
    Permission::findOrCreate('products.{id}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
        ->assertForbidden();
});

test('products list no user', function (): void {
    $this->actingAsGuest();

    $this->get('/products/list')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('products list page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('products.list.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/products/list')
        ->assertOk();
});

test('products list without permission', function (): void {
    Permission::findOrCreate('products.list.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/list')
        ->assertForbidden();
});

test('products no user', function (): void {
    $this->actingAsGuest();

    $this->get('/products/list')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('products without permission', function (): void {
    Permission::findOrCreate('products.list.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/list')
        ->assertForbidden();
});
