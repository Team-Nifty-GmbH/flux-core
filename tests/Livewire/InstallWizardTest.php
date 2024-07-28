<?php

namespace Tests\Feature\Livewire;

use FluxErp\Livewire\InstallWizard;
use Livewire\Livewire;
use Tests\TestCase;

class InstallWizardTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(InstallWizard::class)
            ->assertStatus(200);
    }
}
