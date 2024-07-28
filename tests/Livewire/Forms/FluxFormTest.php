<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\FluxForm;
use Livewire\Livewire;
use Tests\TestCase;

class FluxFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(FluxForm::class)
            ->assertStatus(200);
    }
}
