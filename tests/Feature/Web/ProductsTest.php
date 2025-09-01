<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Category;
use FluxErp\Models\Currency;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;

beforeEach(function (): void {
    $this->product = Product::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    $category = Category::factory()->create([
        'model_type' => Product::class,
    ]);

    Currency::factory()->create(['is_default' => true]);
    PriceList::factory()->create(['is_default' => true]);

    $this->product->categories()->attach($category->id);
});

test('products id no user', function (): void {
    $this->get('/products/' . $this->product->id)
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('products id page', function (): void {
    Currency::factory()->create(['is_default' => true]);

    $this->user->givePermissionTo(Permission::findOrCreate('products.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
        ->assertStatus(200);
});

test('products id product not found', function (): void {
    $this->product->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('products.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
        ->assertStatus(404);
});

test('products id without permission', function (): void {
    Permission::findOrCreate('products.{id}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
        ->assertStatus(403);
});

test('products list no user', function (): void {
    $this->get('/products/list')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('products list page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('products.list.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/products/list')
        ->assertStatus(200);
});

test('products list without permission', function (): void {
    Permission::findOrCreate('products.list.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/list')
        ->assertStatus(403);
});

test('products no user', function (): void {
    $this->get('/products/list')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('products without permission', function (): void {
    Permission::findOrCreate('products.list.get', 'web');

    $this->actingAs($this->user, 'web')->get('/products/list')
        ->assertStatus(403);
});
