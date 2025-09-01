<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Language;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product as ProductModel;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->language = Language::factory()->create(['is_default' => true]);
    $this->vatRate = VatRate::factory()->create(['is_default' => true]);

    $this->priceList = PriceList::factory()->create([
        'is_default' => true,
        'is_net' => true,
    ]);

    $this->product = ProductModel::factory()
        ->for($this->vatRate)
        ->has(
            Price::factory()->state(['price_list_id' => $this->priceList->id]),
            'prices'
        )
        ->create([
            'client_id' => $this->dbClient->getKey(),
            'is_bundle' => false,
        ]);
});

test('delete product', function (): void {
    Livewire::test(Product::class, ['id' => $this->product->id])
        ->call('delete')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertReturned(true)
        ->assertRedirect(route('products.products'));

    $this->assertSoftDeleted('products', ['id' => $this->product->id]);
});

test('get price lists', function (): void {
    $component = Livewire::test(Product::class, ['id' => $this->product->id]);

    $component->call('getPriceLists')
        ->assertStatus(200)
        ->assertHasNoErrors();

    expect($component->get('priceLists'))->not->toBeEmpty();
});

test('get product cross sellings', function (): void {
    $crossSellingProduct = ProductModel::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $crossSelling = ProductCrossSelling::factory()->create([
        'product_id' => $this->product->id,
        'name' => 'Related Products',
    ]);

    $crossSelling->products()->attach($crossSellingProduct->id);

    $component = Livewire::test(Product::class, ['id' => $this->product->id]);

    $component->call('getProductCrossSellings')
        ->assertStatus(200)
        ->assertHasNoErrors();

    expect($component->get('productCrossSellings'))->not->toBeEmpty();
});

test('get tabs', function (): void {
    $component = Livewire::test(Product::class, ['id' => $this->product->id]);
    $tabs = $component->instance()->getTabs();

    expect($tabs)->toBeArray();
    expect($tabs)->not->toBeEmpty();

    // Check that tabs contain expected components
    $tabComponents = collect($tabs)->map(fn ($tab) => $tab->component)->toArray();
    expect($tabComponents)->toContain('product.general');
    expect($tabComponents)->toContain('product.prices');
    expect($tabComponents)->toContain('product.media');
});

test('localize changes language', function (): void {
    $newLanguage = Language::factory()->create();

    Livewire::test(Product::class, ['id' => $this->product->id])
        ->set('languageId', $newLanguage->id)
        ->call('localize')
        ->assertStatus(200)
        ->assertHasNoErrors();

    expect(Session::get('selectedLanguageId'))->toEqual($newLanguage->id);
});

test('mount initializes component', function (): void {
    $component = Livewire::test(Product::class, ['id' => $this->product->id]);

    expect($component->get('product.id'))->toEqual($this->product->id);
    expect($component->get('product.name'))->toEqual($this->product->name);
    expect($component->get('languageId'))->toEqual($this->language->id);
    expect($component->get('languages'))->not->toBeEmpty();
});

test('mount with invalid id fails', function (): void {
    $this->expectException(ModelNotFoundException::class);

    Livewire::test(Product::class, ['id' => 999999]);
});

test('renders successfully', function (): void {
    $component = Livewire::test(Product::class, ['id' => $this->product->id])
        ->assertStatus(200)
        ->assertSet('tab', 'product.general')
        ->assertSet('languageId', $this->language->id);

    expect($component->get('languages'))->not->toBeEmpty();
});

test('reset product', function (): void {
    $component = Livewire::test(Product::class, ['id' => $this->product->id]);

    // Change some values
    $component->set('product.name', 'Changed Name')
        ->set('product.description', 'Changed Description');

    // Reset should restore original values
    $component->call('resetProduct')
        ->assertStatus(200)
        ->assertSet('product.name', $this->product->name)
        ->assertSet('product.description', $this->product->description);
});

test('save product successfully', function (): void {
    Livewire::test(Product::class, ['id' => $this->product->id])
        ->set('product.name', 'Updated Product Name')
        ->set('product.description', 'Updated description')
        ->call('save')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->product->refresh();
    expect($this->product->name)->toEqual('Updated Product Name');
    expect($this->product->description)->toEqual('Updated description');
});

test('save product validation fails', function (): void {
    Livewire::test(Product::class, ['id' => $this->product->id])
        ->set('product.name', '') // Required field
        ->call('save')
        ->assertStatus(200)
        ->assertHasErrors()
        ->assertReturned(false);
});

test('save product with prices', function (): void {
    $component = Livewire::test(Product::class, ['id' => $this->product->id]);

    $component->call('getPriceLists');

    $priceLists = $component->get('priceLists');
    if (! empty($priceLists)) {
        $priceLists[0]['price_net'] = 100.00;
        $priceLists[0]['is_editable'] = true;

        $component->set('priceLists', $priceLists)
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $this->assertDatabaseHas('prices', [
            'product_id' => $this->product->id,
            'price_list_id' => $this->priceList->id,
            'price' => 100.00,
        ]);
    }
});

test('session language persistence', function (): void {
    $newLanguage = Language::factory()->create();
    Session::put('selectedLanguageId', $newLanguage->id);

    $component = Livewire::test(Product::class, ['id' => $this->product->id]);

    expect($component->get('languageId'))->toEqual($newLanguage->id);
});

test('show product properties modal', function (): void {
    Livewire::test(Product::class, ['id' => $this->product->id])
        ->call('showProductPropertiesModal')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertExecutesJs("\$modalOpen('edit-product-properties-modal');");
});

test('switch tabs', function (): void {
    Livewire::test(Product::class, ['id' => $this->product->id])->cycleTabs();
});

test('tab visibility for bundle product', function (): void {
    $bundleProduct = ProductModel::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'is_bundle' => true,
    ]);

    $component = Livewire::test(Product::class, ['id' => $bundleProduct->id]);
    $tabs = $component->instance()->getTabs();

    $bundleTab = collect($tabs)->first(fn ($tab) => $tab->component === 'product.bundle-list');
    expect($bundleTab)->not->toBeNull();
});

test('tab visibility for variant product', function (): void {
    $parentProduct = ProductModel::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'parent_id' => null,
    ]);

    $component = Livewire::test(Product::class, ['id' => $parentProduct->id]);
    $tabs = $component->instance()->getTabs();

    $variantTab = collect($tabs)->first(fn ($tab) => $tab->component === 'product.variant-list');
    expect($variantTab)->not->toBeNull();
});

test('vat rates computed property', function (): void {
    $component = Livewire::test(Product::class, ['id' => $this->product->id]);

    $vatRates = $component->instance()->vatRates();
    expect($vatRates)->toBeArray();
    expect($vatRates)->not->toBeEmpty();
});

test('view name computed property', function (): void {
    $component = Livewire::test(Product::class, ['id' => $this->product->id]);

    $viewName = $component->instance()->viewName();
    expect($viewName)->toBeString();
    $this->assertStringContainsString('product', $viewName);
});
