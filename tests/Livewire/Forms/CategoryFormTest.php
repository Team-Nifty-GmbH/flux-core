<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\CategoryForm;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CategoryForm::class)
            ->assertStatus(200);
    }
}
