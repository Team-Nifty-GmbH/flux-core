<?php

use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\BundleList;
use FluxErp\Models\Pivots\BundleProductProduct;
use FluxErp\Models\Product;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->product = Product::factory()->create(['is_bundle' => false]);
    $this->product->tenants()->attach($this->dbTenant->getKey());

    $this->productForm = new ProductForm(Livewire::new(BundleList::class), 'product');
    $this->productForm->fill($this->product);
});

test('renders successfully', function (): void {
    Livewire::test(BundleList::class, ['product' => $this->productForm])
        ->assertOk();
});

test('edit with model fills form and opens modal', function (): void {
    $bundleProduct = Product::factory()->create();
    $bundleProduct->tenants()->attach($this->dbTenant->getKey());

    $pivot = BundleProductProduct::create([
        'product_id' => $this->product->getKey(),
        'bundle_product_id' => $bundleProduct->getKey(),
        'count' => 3,
    ]);

    Livewire::test(BundleList::class, ['product' => $this->productForm])
        ->call('edit', $pivot->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('productBundleProductForm.pivot_id', $pivot->getKey())
        ->assertSet('productBundleProductForm.bundle_product_id', $bundleProduct->getKey())
        ->assertSet('productBundleProductForm.count', 3)
        ->assertOpensModal('edit-bundle-product-modal');
});

test('can create bundle product', function (): void {
    $bundleProduct = Product::factory()->create();
    $bundleProduct->tenants()->attach($this->dbTenant->getKey());

    Livewire::test(BundleList::class, ['product' => $this->productForm])
        ->set('productBundleProductForm.bundle_product_id', $bundleProduct->getKey())
        ->set('productBundleProductForm.count', 5)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('bundle_product_product', [
        'product_id' => $this->product->getKey(),
        'bundle_product_id' => $bundleProduct->getKey(),
        'count' => 5,
    ]);
});

test('can update bundle product', function (): void {
    $bundleProduct = Product::factory()->create();
    $bundleProduct->tenants()->attach($this->dbTenant->getKey());

    $pivot = BundleProductProduct::create([
        'product_id' => $this->product->getKey(),
        'bundle_product_id' => $bundleProduct->getKey(),
        'count' => 2,
    ]);

    Livewire::test(BundleList::class, ['product' => $this->productForm])
        ->call('edit', $pivot->getKey())
        ->set('productBundleProductForm.count', 10)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect(BundleProductProduct::query()->whereKey($pivot->getKey())->value('count'))->toEqual(10);
});

test('can delete bundle product', function (): void {
    $bundleProduct = Product::factory()->create();
    $bundleProduct->tenants()->attach($this->dbTenant->getKey());

    $pivot = BundleProductProduct::create([
        'product_id' => $this->product->getKey(),
        'bundle_product_id' => $bundleProduct->getKey(),
        'count' => 2,
    ]);

    Livewire::test(BundleList::class, ['product' => $this->productForm])
        ->call('delete', $pivot->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('bundle_product_product', [
        'pivot_id' => $pivot->getKey(),
    ]);
});

test('save validation fails with missing required fields', function (): void {
    Livewire::test(BundleList::class, ['product' => $this->productForm])
        ->set('productBundleProductForm.bundle_product_id', null)
        ->set('productBundleProductForm.count', null)
        ->call('save')
        ->assertOk()
        ->assertReturned(false);
});

test('save sets is_bundle to true on first bundle product', function (): void {
    $bundleProduct = Product::factory()->create();
    $bundleProduct->tenants()->attach($this->dbTenant->getKey());

    expect($this->product->is_bundle)->toBeFalse();

    Livewire::test(BundleList::class, ['product' => $this->productForm])
        ->set('productBundleProductForm.bundle_product_id', $bundleProduct->getKey())
        ->set('productBundleProductForm.count', 1)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true)
        ->assertSet('product.is_bundle', true);
});
