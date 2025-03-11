<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Languages;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class LanguagesTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Languages::class)
            ->assertStatus(200);
    }
}
