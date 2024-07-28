<?php

namespace Tests\Feature\Livewire;

use FluxErp\Livewire\EditMail;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class EditMailTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(EditMail::class)
            ->assertStatus(200);
    }
}
