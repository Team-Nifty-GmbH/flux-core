<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\ProductForm;
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
