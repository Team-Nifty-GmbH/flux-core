<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Category;
use FluxErp\Models\Currency;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;

class ProductsTest extends BaseSetup
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $category = Category::factory()->create([
            'model_type' => Product::class,
        ]);

        Currency::factory()->create(['is_default' => true]);
        PriceList::factory()->create(['is_default' => true]);

        $this->product->categories()->attach($category->id);
    }

    public function test_products_id_no_user(): void
    {
        $this->get('/products/' . $this->product->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_id_page(): void
    {
        Currency::factory()->create(['is_default' => true]);

        $this->user->givePermissionTo(Permission::findOrCreate('products.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
            ->assertStatus(200);
    }

    public function test_products_id_product_not_found(): void
    {
        $this->product->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('products.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
            ->assertStatus(404);
    }

    public function test_products_id_without_permission(): void
    {
        Permission::findOrCreate('products.{id}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
            ->assertStatus(403);
    }

    public function test_products_list_no_user(): void
    {
        $this->get('/products/list')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_list_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('products.list.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/list')
            ->assertStatus(200);
    }

    public function test_products_list_without_permission(): void
    {
        Permission::findOrCreate('products.list.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/list')
            ->assertStatus(403);
    }

    public function test_products_no_user(): void
    {
        $this->get('/products/list')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_without_permission(): void
    {
        Permission::findOrCreate('products.list.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/list')
            ->assertStatus(403);
    }
}
