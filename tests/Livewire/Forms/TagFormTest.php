<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\TagForm;
use Livewire\Livewire;
use Tests\TestCase;

class TagFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(TagForm::class)
            ->assertStatus(200);
    }
}
