<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\EditMail;
use Livewire\Livewire;

class EditMailTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::actingAs($this->user)
            ->test(EditMail::class)
            ->assertStatus(200);
    }
}
