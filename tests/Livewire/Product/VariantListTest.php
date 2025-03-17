<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\VariantList;
use FluxErp\Models\Product;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class VariantListTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create();
    }

    public function test_renders_successfully(): void
    {
        $form = new ProductForm(Livewire::new(VariantList::class), 'product');
        $form->fill($this->product);

        Livewire::test(VariantList::class, ['product' => $form])
            ->assertStatus(200);
    }
}
