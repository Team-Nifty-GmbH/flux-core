<?php

namespace Tests\Feature\Livewire\Forms\Portal;

use FluxErp\Livewire\Forms\Portal\ProductForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProductFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProductForm::class)
            ->assertStatus(200);
    }
}
