<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Category;
use FluxErp\Models\Currency;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductsTest extends BaseSetup
{
    use DatabaseTransactions;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $category = Category::factory()->create([
            'model_type' => Product::class,
        ]);

        Currency::factory()->create(['is_default' => true]);
        PriceList::factory()->create(['is_default' => true]);

        $this->product->categories()->attach($category->id);
    }

    public function test_products_no_user()
    {
        $this->get('/products')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_redirect_dashboard()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('products.get', 'web'));

        $this->actingAs($this->user, guard: 'web')->get('/products')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));
    }

    public function test_products_without_permission()
    {
        Permission::findOrCreate('products.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products')
            ->assertStatus(403);
    }

    public function test_products_list_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('products.list.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/list')
            ->assertStatus(200);
    }

    public function test_products_list_no_user()
    {
        $this->get('/products/list')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_list_without_permission()
    {
        Permission::findOrCreate('products.list.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/list')
            ->assertStatus(403);
    }

    public function test_products_id_page()
    {
        Currency::factory()->create(['is_default' => true]);

        $this->user->givePermissionTo(Permission::findOrCreate('products.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
            ->assertStatus(200);
    }

    public function test_products_id_no_user()
    {
        $this->get('/products/' . $this->product->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_products_id_without_permission()
    {
        Permission::findOrCreate('products.{id}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
            ->assertStatus(403);
    }

    public function test_products_id_product_not_found()
    {
        $this->product->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('products.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/products/' . $this->product->id)
            ->assertStatus(404);
    }
}
