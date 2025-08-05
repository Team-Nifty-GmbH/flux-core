<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\ProductDetail;
use FluxErp\Models\Currency;
use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductDetailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create([
            'is_default' => true,
        ]);
    }

    public function test_renders_successfully(): void
    {
        $product = Product::factory()->create();

        Livewire::test(ProductDetail::class, ['product' => $product])
            ->assertStatus(200)
            ->assertSet('productForm.id', $product->id)
            ->assertSet('productForm.parent_id', $product->parent_id)
            ->assertSet('productForm.product_number', $product->product_number)
            ->assertSet('productForm.name', $product->name)
            ->assertSet('productForm.description', $product->description)
            ->assertSee([
                $product->product_number,
                $product->name,
                $product->description,
            ]);
    }

    public function test_shows_bundle_products(): void
    {
        $product = Product::factory()
            ->has(
                Product::factory()
                    ->has(Product::factory()->count(2), 'bundleProducts')
                    ->state([
                        'is_nos' => true,
                        'is_active_export_to_web_shop' => true,
                        'is_active' => true,
                    ])
                    ->count(3),
                'bundleProducts'
            )
            ->state([
                'is_nos' => true,
                'is_active_export_to_web_shop' => true,
                'is_active' => true,
            ])
            ->create();

        Livewire::test(ProductDetail::class, ['product' => $product])
            ->assertStatus(200)
            ->assertSet('productForm.id', $product->id)
            ->assertSet('productForm.parent_id', $product->parent_id)
            ->assertSet('productForm.product_number', $product->product_number)
            ->assertCount('productForm.bundle_products', 9)
            ->assertSee([
                $product->product_number,
                $product->name,
                $product->description,
            ])
            ->assertSee($product->bundleProducts()->pluck('name')->toArray());
    }

    public function test_shows_cross_selling_products(): void
    {
        $product = Product::factory()
            ->has(
                ProductCrossSelling::factory()
                    ->has(
                        Product::factory()
                            ->state([
                                'is_nos' => true,
                                'is_active_export_to_web_shop' => true,
                                'is_active' => true,
                            ])
                            ->count(3),
                        'products'
                    )
                    ->state([
                        'is_active' => true,
                    ])
                    ->count(3),
                'productCrossSellings'
            )
            ->state([
                'is_nos' => true,
                'is_active_export_to_web_shop' => true,
                'is_active' => true,
            ])
            ->create();

        Livewire::withoutLazyLoading()
            ->test(ProductDetail::class, ['product' => $product])
            ->assertStatus(200)
            ->assertSet('productForm.id', $product->id)
            ->assertSet('productForm.parent_id', $product->parent_id)
            ->assertSet('productForm.product_number', $product->product_number)
            ->assertCount('productForm.product_cross_sellings', 3)
            ->assertSee([
                $product->product_number,
                $product->name,
                $product->description,
            ])
            ->assertSee($product->productCrossSellings->pluck('name')->toArray())
            ->assertSee(
                $product->productCrossSellings
                    ->flatMap(fn (ProductCrossSelling $productCrossSelling) => $productCrossSelling
                        ->products
                        ->pluck('name')
                    )
                    ->toArray()
            );
    }
}
