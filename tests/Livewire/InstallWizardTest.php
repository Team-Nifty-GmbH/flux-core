<?php

namespace Tests\Feature\Livewire;

use FluxErp\Livewire\InstallWizard;
use FluxErp\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

class InstallWizardTest extends TestCase
{
    public function test_renders_successfully()
    {
        // Set the configuration value
        Config::set('flux.install_done', false);

        // Ensure the configuration is correctly set
        $this->assertFalse(Config::get('flux.install_done'));

        Livewire::test(InstallWizard::class)
            ->assertStatus(200);
    }

    public function test_forbidden_when_done()
    {
        Livewire::test(InstallWizard::class)
            ->assertStatus(403);
    }
}
