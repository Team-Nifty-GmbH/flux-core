<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\ProjectForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProjectForm::class)
            ->assertStatus(200);
    }
}
