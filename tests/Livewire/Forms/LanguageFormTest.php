<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\LanguageForm;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(LanguageForm::class)
            ->assertStatus(200);
    }
}
