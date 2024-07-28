<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\ProductBundleProductForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProductBundleProductFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProductBundleProductForm::class)
            ->assertStatus(200);
    }
}
