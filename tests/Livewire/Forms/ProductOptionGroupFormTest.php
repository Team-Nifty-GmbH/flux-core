<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\ProductOptionGroupForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProductOptionGroupFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProductOptionGroupForm::class)
            ->assertStatus(200);
    }
}
