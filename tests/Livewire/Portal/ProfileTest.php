<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Profile;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProfileTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Profile::class)
            ->assertStatus(200);
    }
}
