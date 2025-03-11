<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Files;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class FilesTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Files::class)
            ->assertStatus(200);
    }
}
