<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Language;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product as ProductModel;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

class ProductTest extends BaseSetup
{
    protected Language $language;

    protected string $livewireComponent = Product::class;

    protected PriceList $priceList;

    protected ProductModel $product;

    protected VatRate $vatRate;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_additional_columns_populated(): void
    {
        $component = Livewire::test(Product::class, ['id' => $this->product->id]);

        $additionalColumns = $component->get('additionalColumns');
        $this->assertIsArray($additionalColumns);
    }

    public function test_delete_product(): void
    {
        Livewire::test(Product::class, ['id' => $this->product->id])
            ->call('delete')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true)
            ->assertRedirect(route('products.products'));

        $this->assertSoftDeleted('products', ['id' => $this->product->id]);
    }

    public function test_get_price_lists(): void
    {
        $component = Livewire::test(Product::class, ['id' => $this->product->id]);

        $component->call('getPriceLists')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertNotEmpty($component->get('priceLists'));
    }

    public function test_get_product_cross_sellings(): void
    {
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

        $this->assertNotEmpty($component->get('productCrossSellings'));
    }

    public function test_get_tabs(): void
    {
        $component = Livewire::test(Product::class, ['id' => $this->product->id]);
        $tabs = $component->instance()->getTabs();

        $this->assertIsArray($tabs);
        $this->assertNotEmpty($tabs);

        // Check that tabs contain expected components
        $tabComponents = collect($tabs)->map(fn ($tab) => $tab->component)->toArray();
        $this->assertContains('product.general', $tabComponents);
        $this->assertContains('product.prices', $tabComponents);
        $this->assertContains('product.media', $tabComponents);
    }

    public function test_localize_changes_language(): void
    {
        $newLanguage = Language::factory()->create();

        Livewire::test(Product::class, ['id' => $this->product->id])
            ->set('languageId', $newLanguage->id)
            ->call('localize')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertEquals($newLanguage->id, Session::get('selectedLanguageId'));
    }

    public function test_mount_initializes_component(): void
    {
        $component = Livewire::test(Product::class, ['id' => $this->product->id]);

        $this->assertEquals($this->product->id, $component->get('product.id'));
        $this->assertEquals($this->product->name, $component->get('product.name'));
        $this->assertEquals($this->language->id, $component->get('languageId'));
        $this->assertNotEmpty($component->get('languages'));
    }

    public function test_mount_with_invalid_id_fails(): void
    {
        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Product::class, ['id' => 999999]);
    }

    public function test_renders_successfully(): void
    {
        $component = Livewire::test(Product::class, ['id' => $this->product->id])
            ->assertStatus(200)
            ->assertSet('tab', 'product.general')
            ->assertSet('languageId', $this->language->id);

        $this->assertNotEmpty($component->get('languages'));
    }

    public function test_reset_product(): void
    {
        $component = Livewire::test(Product::class, ['id' => $this->product->id]);

        // Change some values
        $component->set('product.name', 'Changed Name')
            ->set('product.description', 'Changed Description');

        // Reset should restore original values
        $component->call('resetProduct')
            ->assertStatus(200)
            ->assertSet('product.name', $this->product->name)
            ->assertSet('product.description', $this->product->description);
    }

    public function test_save_product_successfully(): void
    {
        Livewire::test(Product::class, ['id' => $this->product->id])
            ->set('product.name', 'Updated Product Name')
            ->set('product.description', 'Updated description')
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertReturned(true);

        $this->product->refresh();
        $this->assertEquals('Updated Product Name', $this->product->name);
        $this->assertEquals('Updated description', $this->product->description);
    }

    public function test_save_product_validation_fails(): void
    {
        Livewire::test(Product::class, ['id' => $this->product->id])
            ->set('product.name', '') // Required field
            ->call('save')
            ->assertStatus(200)
            ->assertHasErrors()
            ->assertReturned(false);
    }

    public function test_save_product_with_prices(): void
    {
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
    }

    public function test_session_language_persistence(): void
    {
        $newLanguage = Language::factory()->create();
        Session::put('selectedLanguageId', $newLanguage->id);

        $component = Livewire::test(Product::class, ['id' => $this->product->id]);

        $this->assertEquals($newLanguage->id, $component->get('languageId'));
    }

    public function test_show_product_properties_modal(): void
    {
        Livewire::test(Product::class, ['id' => $this->product->id])
            ->call('showProductPropertiesModal')
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertExecutesJs("\$modalOpen('edit-product-properties-modal');");
    }

    public function test_switch_tabs(): void
    {
        Livewire::test(Product::class, ['id' => $this->product->id])->cycleTabs();
    }

    public function test_tab_visibility_for_bundle_product(): void
    {
        $bundleProduct = ProductModel::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'is_bundle' => true,
        ]);

        $component = Livewire::test(Product::class, ['id' => $bundleProduct->id]);
        $tabs = $component->instance()->getTabs();

        $bundleTab = collect($tabs)->first(fn ($tab) => $tab->component === 'product.bundle-list');
        $this->assertNotNull($bundleTab);
    }

    public function test_tab_visibility_for_variant_product(): void
    {
        $parentProduct = ProductModel::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'parent_id' => null,
        ]);

        $component = Livewire::test(Product::class, ['id' => $parentProduct->id]);
        $tabs = $component->instance()->getTabs();

        $variantTab = collect($tabs)->first(fn ($tab) => $tab->component === 'product.variant-list');
        $this->assertNotNull($variantTab);
    }

    public function test_vat_rates_computed_property(): void
    {
        $component = Livewire::test(Product::class, ['id' => $this->product->id]);

        $vatRates = $component->instance()->vatRates();
        $this->assertIsArray($vatRates);
        $this->assertNotEmpty($vatRates);
    }

    public function test_view_name_computed_property(): void
    {
        $component = Livewire::test(Product::class, ['id' => $this->product->id]);

        $viewName = $component->instance()->viewName();
        $this->assertIsString($viewName);
        $this->assertStringContainsString('product', $viewName);
    }
}
