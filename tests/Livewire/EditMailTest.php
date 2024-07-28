<?php

namespace Tests\Feature\Livewire;

use FluxErp\Livewire\EditMail;
use Livewire\Livewire;
use Tests\TestCase;

class EditMailTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(EditMail::class)
            ->assertStatus(200);
    }
}
